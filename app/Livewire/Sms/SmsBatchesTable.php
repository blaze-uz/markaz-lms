<?php

namespace App\Livewire\Sms;

use App\Models\SmsBatch;
use Livewire\Component;
use Livewire\WithPagination;

class SmsBatchesTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $currentBranchId = auth()->user()?->current_branch_id;

        $batches = SmsBatch::query()
            ->with(['template', 'creator'])
            ->withCount([
                'sms',
                'sms as sent_sms_count' => fn ($query) => $query->where('status', 'sent'),
                'sms as pending_sms_count' => fn ($query) => $query->whereIn('status', ['pending', 'waiting']),
                'sms as sending_sms_count' => fn ($query) => $query->where('status', 'sending'),
                'sms as failed_sms_count' => fn ($query) => $query->where('status', 'failed'),
                'sms as scheduled_sms_count' => fn ($query) => $query->where('status', 'scheduled'),
            ])
            ->withSum('sms as actual_price', 'total_price')
            ->when($currentBranchId, fn ($query) => $query->where('branch_id', $currentBranchId))
            ->latest()
            ->paginate(5, ['*'], 'batchPage');

        return view('livewire.sms.sms-batches-table', [
            'batches' => $batches,
        ]);
    }

    public function recipientScopeLabel(string $scope): string
    {
        return match ($scope) {
            'students_and_parents' => "O'quvchi + ota-ona",
            'parents' => 'Ota-ona',
            default => "O'quvchi",
        };
    }

    public function sendSmsTypeLabel(string $type): string
    {
        return match ($type) {
            'staff' => 'Xodim',
            'leads' => 'Lid',
            default => "O'quvchi",
        };
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'scheduled' => __('translation.sms.scheduled'),
            'sending' => __('translation.sms.sending'),
            'sent' => __('translation.sms.sent'),
            'failed' => __('translation.sms.failed'),
            default => __('translation.sms.pending'),
        };
    }

    public function statusClass(string $status): string
    {
        return match ($status) {
            'scheduled' => 'bg-primary-subtle text-primary',
            'sending' => 'bg-info-subtle text-info',
            'sent' => 'bg-success-subtle text-success',
            'failed' => 'bg-danger-subtle text-danger',
            default => 'bg-warning-subtle text-warning',
        };
    }
}
