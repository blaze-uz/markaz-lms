@props(['item'])

<div class="modal fade" id="moreInfoModal" tabindex="-1" aria-labelledby="moreInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moreInfoModalLabel">
                    {{ $item->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table mb-0 table-borderless">
                        <tbody>
                            <tr>
                                <th>
                                    <span class="fw-medium">@lang("attr.create_date")</span>
                                </th>
                                <td>
                                    {{ dateFormat($item->created_at) }}
                                </td>
                            </tr>
                            @if($item->state != 'completed')
                            <x-groups.payment-info :item="$item" />
                            @if($item->state == 'active')
                            <tr>
                                <th>
                                    <span class="fw-medium">@lang("translation.groups.automatically_send_attendance_notice_to_students")</span>
                                </th>
                                <td>
                                    <x-badge variant="{{ $item->attendanceSettings && $item->attendanceSettings->auto_send ? 'success' : 'danger' }}">
                                        @lang($item->attendanceSettings && $item->attendanceSettings->auto_send ? "translation.on" : "translation.off")
                                    </x-badge>
                                </td>
                            </tr>
                            @endif
                            @else
                            <tr>
                                <th>
                                    <span class="fw-medium">@lang("translation.groups.completed_at")</span>
                                </th>
                                <td>
                                    {{ dateFormat($item->completed_at) ?? '-' }}
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang("translation.Close")</button>
            </div>
        </div>
    </div>
</div>
