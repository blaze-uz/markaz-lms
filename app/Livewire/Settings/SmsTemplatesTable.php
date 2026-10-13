<?php

namespace App\Livewire\Settings;

use App\Models\SmsTemplate;
use Livewire\Component;
use Livewire\WithPagination;

class SmsTemplatesTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search = '';
    public $selectedStatus = '';

    public function render()
    {
        $query = SmsTemplate::search($this->search);

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        $templates = $query->orderByDesc('id')->paginate(10);
        $statuses = SmsTemplate::getStatuses();

        return view('livewire.settings.sms-templates-table', compact('templates', 'statuses'));
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus()
    {
        $this->resetPage();
    }
}
