<?php

namespace App\Livewire\Leads;

use App\Models\KanbanTaskCall;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CallsIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $date;
    public $search = '';
    public $statusFilter = 'all';

    public function mount()
    {
        $this->date = Carbon::now()->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
    }

    public function render()
    {
        $dates = explode(" - ", $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->format('Y-m-d');
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->format('Y-m-d');
        $items = KanbanTaskCall::with(['causer', 'lead'])
            //search by lead name
            ->whereHas('lead', function ($query) {
                $query->with('causer')
                    ->where('name', 'ilike', '%' . $this->search . '%')
                    ->orWhere(DB::raw("replace(replace(replace(phone,'(',''),')',''),'-','')"), 'ilike', '%' . $this->search . '%');
            })
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->when($this->statusFilter !== 'all', function ($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        $cardData = $this->getCardData($startDate, $endDate);

        return view('livewire.leads.calls-index', [
            'items' => $items,
            'cardData' => $cardData,
        ]);
    }

    private function getCardData(string $startDate, string $endDate)
    {
        $data = KanbanTaskCall::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->get();
        $total = $data->count();
        $answered = $data->where('status', 'answered')->count();
        $declined = $data->where('status', 'declined')->count();
        return [
            'total' => $total,
            'answered' => $answered,
            'declined' => $declined,
        ];
    }
}
