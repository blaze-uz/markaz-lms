<?php

namespace App\Livewire\Telegram;

use App\Jobs\SendTelegramMessage;
use App\Models\Student;
use App\Models\TelegramMessage;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class TelegramMessagesTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filterByStatus = 'all';

    public string $date;

    public $perPage = 25;

    public $search = '';

    // Send message modal properties
    public $showSendModal = false;

    public $selectedStudents = [];

    public $studentSearch = '';

    public $message = '';

    public $isBotConfigured = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterByStatus' => ['except' => 'all'],
        'date' => ['except' => ''],
        'perPage' => ['except' => 25],
    ];

    public function mount()
    {
        $today = now()->format('Y.m.d');
        $this->date = "{$today} - {$today}";
        $this->checkBotConfiguration();
    }

    public function updatedFilterByStatus()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openSendModal()
    {
        $this->showSendModal = true;
        $this->selectedStudents = [];
        $this->message = '';
        $this->studentSearch = '';
    }

    public function closeSendModal()
    {
        $this->showSendModal = false;
        $this->selectedStudents = [];
        $this->message = '';
        $this->studentSearch = '';
    }

    public function toggleStudent($studentId)
    {
        $key = array_search($studentId, $this->selectedStudents);
        if ($key !== false) {
            unset($this->selectedStudents[$key]);
            $this->selectedStudents = array_values($this->selectedStudents);
        } else {
            $this->selectedStudents[] = $studentId;
        }
    }

    public function checkBotConfiguration(): void
    {
        $studentBotToken = settings('student_telegram_bot_token');

        if (empty($studentBotToken)) {
            $this->isBotConfigured = false;

            return;
        }

        $bot = TelegraphBot::where('token', $studentBotToken)->first();
        $this->isBotConfigured = (bool) $bot;
    }

    public function sendMessage()
    {
        abort_unless(auth()->user()?->can('sms-store'), 403);

        $this->validate([
            'message' => 'required|string|min:1',
            'selectedStudents' => 'required|array|min:1',
        ], [
            'message.required' => __('translation.telegram.message_required'),
            'selectedStudents.required' => __('translation.please_select_students'),
        ]);

        $studentBotToken = settings('student_telegram_bot_token');

        if (empty($studentBotToken)) {
            session()->flash('error_alert', __('translation.telegram.student_bot_not_configured'));

            return;
        }

        $bot = TelegraphBot::where('token', $studentBotToken)->first();

        if (! $bot) {
            session()->flash('error_alert', __('translation.telegram.student_bot_not_found'));

            return;
        }

        $students = Student::whereIn('id', $this->selectedStudents)->get();
        $formattedMessage = $this->formatMessage($this->message);
        $branchId = auth()->user()->current_branch_id;
        $createdById = auth()->id();

        $dispatchedCount = 0;
        $delay = 0; // Delay in seconds for staggering job dispatch

        foreach ($students as $index => $student) {
            // Get student's branch or use current user's branch
            $studentBranchId = $student->branches()->first()?->id ?? $branchId;

            // Dispatch job with delay to prevent overwhelming the queue
            // Telegram allows 30 messages/second, so we stagger by 0.05 seconds (50ms) = 20 messages/second
            SendTelegramMessage::dispatch(
                $student->id,
                $formattedMessage,
                $bot->id,
                $studentBranchId,
                $createdById
            )->delay(now()->addSeconds($delay))->onQueue('sms');

            $dispatchedCount++;
            $delay += 0.05; // 50ms delay between dispatches = 20 messages/second max
        }

        $this->closeSendModal();
        $this->resetPage();

        session()->flash('success_alert', __('translation.telegram.messages_queued', ['count' => $dispatchedCount]));
    }

    private function formatMessage(string $message): string
    {
        return $message;
    }

    private function getDateRange(): array
    {
        $dates = explode(' - ', $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->startOfDay();
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->endOfDay();

        return [$startDate, $endDate];
    }

    public function render()
    {
        [$startDate, $endDate] = $this->getDateRange();

        $messages = TelegramMessage::with(['student', 'createdBy'])
            ->when($this->filterByStatus !== 'all', function ($query) {
                $query->where('status', $this->filterByStatus);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->whereHas('student', function ($q) {
                        $q->where('name', 'ilike', '%'.$this->search.'%')
                            ->orWhere('surname', 'ilike', '%'.$this->search.'%')
                            ->orWhere('phone', 'ilike', '%'.$this->search.'%');
                    })
                        ->orWhere('message', 'ilike', '%'.$this->search.'%');
                });
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $searchableStudents = [];
        if ($this->showSendModal && strlen($this->studentSearch) >= 2) {
            $searchableStudents = Student::where(function ($query) {
                $terms = array_filter(explode(' ', trim($this->studentSearch)));
                if (count($terms) >= 2) {
                    $query->where(function ($q) use ($terms) {
                        $q->where(function ($inner) use ($terms) {
                            $inner->where('name', 'ilike', '%'.$terms[0].'%')
                                ->where('surname', 'ilike', '%'.$terms[1].'%');
                        })->orWhere(function ($inner) use ($terms) {
                            $inner->where('surname', 'ilike', '%'.$terms[0].'%')
                                ->where('name', 'ilike', '%'.$terms[1].'%');
                        });
                    });
                } else {
                    $query->where('name', 'ilike', '%'.$this->studentSearch.'%')
                        ->orWhere('surname', 'ilike', '%'.$this->studentSearch.'%')
                        ->orWhere('phone', 'ilike', '%'.$this->studentSearch.'%');
                }
            })
                ->with('groups')
                ->limit(20)
                ->get();
        }

        return view('livewire.telegram.telegram-messages-table', [
            'messages' => $messages,
            'searchableStudents' => $searchableStudents,
        ]);
    }
}
