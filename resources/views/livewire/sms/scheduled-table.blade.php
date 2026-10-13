<div>
    @push('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    @endpush
    @if(count($selectedUsers) > 0)
    <div class="alert bg-primary-subtle text-primary">
        <i class="mdi mdi-information"></i>
        @lang("translation.sms.you_can_delete_only_pending_sms")
    </div>
    @endif
    <div class="flex-wrap gap-3 d-flex justify-content-between">
        <div>
            <form action="{{ route('admin.sms.send-scheduled') }}" method="POST" class="d-inline-block">
                @csrf
                <button type="submit" class="btn btn-primary"
                    onclick="return confirm('Barcha smslar yuboriladi! Ishonchingiz komilmi?')">
                    @lang("translation.sms.send_pending_sms")
                    <i class="mdi mdi-send"></i>
                </button>
            </form>
        </div>
        <div class="gap-3 mb-3 d-flex justify-content-end">
            <div @class(['d-none'=> count($selectedUsers) === 0, 'gap-2 d-flex align-items-center'])>
                <span class="badge bg-danger-subtle text-danger">
                    {{ count($selectedUsers) }} ta tanlangan
                </span>
                <form action="{{ route('admin.sms.delete') }}" method="POST" class="d-inline-block">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="ids" value="{{ implode(',', $selectedUsers) }}">
                    <button type="submit" class="btn btn-soft-danger">
                        <i class="ri-delete-bin-2-line"></i>
                    </button>
                </form>
                <form action="{{ route('admin.sms.return-to-pending') }}" method="POST" class="d-inline-block">
                    @csrf
                    <input type="hidden" name="ids" value="{{ implode(',', $selectedUsers) }}">
                    <button type="submit" class="btn btn-soft-warning" data-bs-toggle="tooltip"
                        title="{{__('translation.return_to_pending')}}" wire:ignore>
                        <i class="ri-arrow-go-back-line"></i>
                    </button>
                </form>
            </div>
            <div class="gap-3 d-flex justify-content-end">
                <button type="button" class="mr-3 bg-white border btn" id="sms-date-range" data-livewire="@this">
                    <i class="far fa-calendar-alt"></i>
                    <span>
                        {{ $date ? $date : "Select" }}
                    </span>
                    <i class="fas fa-caret-down"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault"
                                    wire:model.live="selectAll" wire:change="selectAll"
                                    @if(count($selectedUsers)===count($sms)) checked @endif />
                            </div>
                        </th>
                        <th>ID</th>
                        <th>@lang("translation.sms.receiver_name")</th>
                        <th>@lang("translation.students.phone")</th>
                        <th>@lang("translation.sms.content")</th>
                        <th>@lang("translation.sms.send_sms_type")</th>
                        <th>@lang("translation.sms.character_count")</th>
                        <th>@lang("attr.status")</th>
                        <th>@lang("translation.sms.left_time_to_send")</th>
                        <th>@lang("translation.sms.scheduled_at")</th>
                        <th></th>
                    </tr>
                    @forelse($sms as $item)
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sms_{{ $item->id }}"
                                    wire:model.live="selectedUsers" value="{{ $item->id }}" />
                            </div>
                        </td>
                        <td>{{ $item->id }}</td>
                        <td class="text-nowrap">{{ $item->smsable?->name .' '.$item->smsable?->surname }}</td>
                        <td class="text-nowrap">{{ $item->phone }}</td>
                        <td class="text-small moreLess">{{ Str::limit($item->content, 50) }}</td>
                        <td class="text-nowrap">
                            <span class="text-success">
                                {{ __('translation.sms.'.$item->sms_type) }}
                                <i class="ri-mail-send-line"></i>
                            </span>
                        </td>
                        <td class="text-nowrap">{{ $item->character_count }}</td>
                        <td class="text-nowrap">
                            <span class="badge bg-primary-subtle text-primary">
                                {{ __('translation.sms.scheduled_status') }}
                            </span>
                        </td>
                        <td class="text-nowrap">{{ Carbon\Carbon::parse($item->scheduled_at)->diffForHumans() }}</td>
                        <td class="text-nowrap">{{ dateFormat($item->scheduled_at, 'd.m.Y H:i') }}</td>
                        <td>
                            <div class="gap-2 d-flex align-items-center text-nowrap">
                                <form action="{{ route('admin.sms.send-single', $item->id) }}" method="POST"
                                    class="d-inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" data-bs-toggle="tooltip"
                                        title="{{__('translation.sms.send_now')}}"
                                        onclick="return confirm('Are you sure?')">
                                        <i class="ri-send-plane-2-line"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.sms.show', $item) }}" class="btn btn-sm btn-primary"
                                    data-bs-toggle="tooltip" title="{{__('translation.btn.show')}}">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                                <form action="{{ route('admin.sms.delete') }}" method="POST" class="d-inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="ids" value="{{ $item->id }}">
                                    <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                        title="{{__('translation.btn.delete')}}">
                                        <i class="ri-close-circle-line"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="100">
                            <x-no-result />
                        </td>
                    </tr>
                    @endforelse
                </table>
                <div class="d-flex justify-content-end">
                    {{ $sms->links() }}
                </div>
            </div>
        </div>
        <div class="card-preloader d-none" wire:loading.class.remove="d-none">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
    @push("custom-scripts")
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js">
    </script>
    <script>
        //set locale moment
            moment.locale('{{app()->getLocale() == "ru" ? "ru" : "uz-latn"}}');
            //Date range as a button
            $('#sms-date-range').daterangepicker({
                    ranges: {
                        "{{__('attr.today')}}": [moment(), moment()],
                        "{{__('attr.yesterday')}}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        "{{__('attr.last7days')}}": [moment().subtract(6, 'days'), moment()],
                        "{{__('attr.last30days')}}": [moment().subtract(29, 'days'), moment()],
                        "{{__('attr.thisMonth')}}": [moment().startOf('month'), moment().endOf('month')],
                        "{{__('attr.lastMonth')}}": [
                            moment().subtract(1, 'months').startOf('month'),
                            moment().subtract(1, 'months').endOf('month')
                        ],
                        "{{__('attr.thisYear')}}": [moment().startOf('year'), moment().endOf('year')],
                        "{{__('attr.lastYear')}}": [
                            moment().subtract(1, 'years').startOf('year'),
                            moment().subtract(1, 'years').endOf('year')
                        ],
                    },
                    startDate: moment(),
                    endDate: moment(),
                    locale: {
                        "customRangeLabel": "{{__('attr.optionalDate')}}",
                        "applyLabel": "{{__('attr.save')}}",
                        "cancelLabel": "{{__('attr.cancel')}}",
                    }
                },
                function (start, end) {
                    let selected = start.format('YYYY.MM.DD') + ' - ' + end.format('YYYY.MM.DD');
                    eval($('#sms-date-range').attr('data-livewire')).set('date', selected);
                    $('#sms-date-range span').html(selected);
                }
            )
    </script>
    @endpush
</div>