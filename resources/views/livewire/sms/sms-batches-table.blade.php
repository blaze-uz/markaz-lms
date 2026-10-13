<div>
    <div class="card">
        <div class="border-0 card-header">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">SMS batchlar</h5>
                <span class="badge bg-info-subtle text-info">{{ $batches->total() }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-sm">
                    <thead>
                        <tr class="text-muted">
                            <th>Vaqt</th>
                            <th>Template</th>
                            <th>Turi</th>
                            <th>Qabul qiluvchi</th>
                            <th class="text-end">SMS qism</th>
                            <th class="text-end">Belgilar</th>
                            <th class="text-end">Haqiqiy</th>
                            <th>Status</th>
                            <th>Yaratgan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $batch)
                            <tr wire:key="sms-batch-{{ $batch->id }}">
                                <td class="text-nowrap">
                                    <div>{{ dateFormat($batch->created_at, 'd.m.Y H:i') }}</div>
                                    @if ($batch->scheduled_at)
                                        <small class="text-primary">{{ dateFormat($batch->scheduled_at, 'd.m.Y H:i') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-wrap" style="max-width: 240px;">
                                        <span data-bs-toggle="tooltip" title="{{ $batch->template_content }}">
                                            {{ Str::limit($batch->template_name ?? $batch->template?->name ?? 'Custom', 45) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-nowrap">
                                    <div>{{ $this->sendSmsTypeLabel($batch->send_sms_type) }}</div>
                                    <small class="text-muted">
                                        {{ $this->recipientScopeLabel($batch->recipient_scope) }} /
                                        {{ $batch->sms_type === 'reklama' ? 'Reklama' : 'Xizmat' }}
                                    </small>
                                </td>
                                <td class="text-nowrap">
                                    <div>{{ $batch->recipient_count }} ta</div>
                                    <small class="text-muted">{{ $batch->selected_receivers_count }} tanlangan</small>
                                </td>
                                <td class="text-end text-nowrap">
                                    <div>{{ $batch->sms_parts_count }}</div>
                                    @if ($batch->min_parts_per_message || $batch->max_parts_per_message)
                                        <small class="text-muted">
                                            {{ $batch->min_parts_per_message }}-{{ $batch->max_parts_per_message }} /
                                            {{ $batch->encoding === 'unicode' ? 'Kirill' : 'Lotin' }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    {{ $batch->template_character_count }}
                                </td>
                                <td class="text-end text-nowrap">
                                    {{ $batch->actual_price ? number_format($batch->actual_price, 0, '.', ' ') : '-' }}
                                </td>
                                <td class="text-nowrap">
                                    <span class="badge {{ $this->statusClass($batch->status) }}">
                                        {{ $this->statusLabel($batch->status) }}
                                    </span>
                                    <div class="mt-1 small text-muted">
                                        {{ $batch->sent_sms_count }} sent /
                                        {{ $batch->failed_sms_count }} failed /
                                        {{ $batch->pending_sms_count + $batch->sending_sms_count + $batch->scheduled_sms_count }} open
                                    </div>
                                </td>
                                <td class="text-nowrap">
                                    {{ $batch->creator?->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <x-no-result />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $batches->links() }}
            </div>
        </div>
    </div>
</div>
