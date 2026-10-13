<div>
    @push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    @endpush
    <div class="card">
        <div class="flex-wrap gap-2 card-body d-flex align-items-center justify-content-between">
            <div class="row g-2">
                <div class="col-12 col-lg-3">
                    <input type="text" class="form-control" placeholder="{{__('translation.search')}}"
                        wire:model.live.debounce.500ms="search">
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <select class="form-control form-select" wire:model.live="selectedChosenDays">
                        <option value="" disabled selected>{{__("translation.leads.chosen_days")}}</option>
                        <option value="">{{__("translation.leads.all")}}</option>
                        <option value="odd">{{__("translation.leads.odd")}}</option>
                        <option value="even">{{__("translation.leads.even")}}</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <select class="form-control form-select select2 teachers" wire:model.live="selectedTeacher">
                        <option value="" selected>{{__("translation.leads.teacher")}}</option>
                        @foreach ($teachers as $teacher)
                        <option value="{{$teacher->id}}">{{$teacher->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <select class="form-control form-select select2 boards" wire:model.live="selectedBoard">
                        <option value="" selected>{{__("translation.leads.board")}}</option>
                        @foreach ($boards as $board)
                        <option value="{{$board->id}}">{{$board->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <select class="form-control form-select select2 causers" wire:model.live="selectedCauser">
                        <option value="" selected>{{__("translation.leads.causer")}}</option>
                        @foreach ($causers as $causer)
                        <option value="{{$causer->id}}">{{$causer->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <button type="button" class="border btn btn-light w-100" id="daterange-btn-debts"
                        data-livewire="@this">
                        <i class="far fa-calendar-alt me-2"></i>
                        <span>{{$date}}</span>
                    </button>
                </div>
            </div>
            <div class="gap-2 d-flex justify-content-end">
                <button wire:click="export" class="btn btn-success">
                    <i class="fa-solid fa-file-export"></i>
                    @lang("translation.export")
                </button>
                <a href="{{ route('admin.leads.board') }}" class="btn btn-primary">
                    <i class="fa-solid fa-table-columns"></i>
                    @lang("translation.leads.board")
                </a>
                <a href="{{ route('admin.leads.settings') }}" class="btn btn-info">
                    <i class="fas fa-gear"></i> @lang("translation.leads.settings")
                </a>
                <div class="dropdown">
                    <button class="btn btn-success" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-plus"></i> @lang("translation.leads.create_lead")
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{ route('admin.leads.create') }}" class="dropdown-item">
                                <i class="fas fa-plus"></i> @lang("translation.btn.add")
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.leads.import') }}" class="dropdown-item">
                                <i class="fa-regular fa-file-excel"></i>
                                @lang("translation.leads.import_from_excel")
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>@lang("translation.leads.name")</th>
                        <th>@lang("translation.leads.phone")</th>
                        <th>@lang("translation.leads.board")</th>
                        <th>@lang("translation.leads.chosen_time")</th>
                        <th>@lang("translation.leads.teacher")</th>
                        <th>@lang("translation.courses.course")</th>
                        <th>@lang("translation.leads.attend")</th>
                        <th>@lang("translation.leads.date")</th>
                        <th>@lang("translation.actions")</th>
                    </tr>
                    @forelse($leads as $leadItem)
                    <tr>
                        <td>{{$leadItem->id}}</td>
                        <td>
                            {{$leadItem->name}}
                            @if($leadItem->reminders->count())
                            <span class="badge bg-warning-subtle text-warning" data-bs-toggle="tooltip"
                                title="{{__('translation.leads.has_reminder')}}">
                                <i class="fa-solid fa-bell"></i>
                            </span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="tel:{{$leadItem->phone}}">{{$leadItem->phone}}</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.boards.show', $leadItem->kanban_board_id ?? 0) }}">
                                {{$leadItem->board->name ?? '-'}}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-success-subtle text-success">
                                {{ $leadItem->chosen_days ? __('translation.leads.'.$leadItem->chosen_days) :
                                __('translation.leads.other')}}
                            </span><br>
                            <span class="badge bg-success-subtle text-success">
                                {{$leadItem->chosen_time ? dateFormat($leadItem->chosen_time, 'H:i') : '-'}}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            @foreach($leadItem->teachers as $teacher)
                            <span>{{$teacher->name}}</span>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('admin.courses.show', $leadItem->course_id ?? 0) }}">
                                {{$leadItem->course->name ?? '-'}}
                            </a>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault"
                                    @if($leadItem->attend) checked @endif
                                wire:change="handleAttend({{$leadItem->id}})"/>
                            </div>
                        </td>
                        <td>
                            <div class="cursor-pointer d-flex flex-column" data-bs-toggle="tooltip"
                                title="{{dateFormat($leadItem->created_at, 'd.m.Y H:i')}}">
                                <span>{{dateFormat($leadItem->created_at)}}</span>
                                <small class="text-muted">{{$leadItem->causer?->name ?? 'Causer not found'}}</small>
                            </div>
                        </td>
                        <td class="text-nowrap">
                            <a class="btn text-primary" data-bs-toggle="tooltip"
                                wire:click="clickTask({{$leadItem->id}})" title="{{__('translation.show')}}">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a class="btn text-primary" wire:click="handleCall({{$leadItem->id}})"
                                data-bs-toggle="tooltip" title="{{__('translation.leads.call')}}">
                                <i class="fa-solid fa-phone"></i>
                            </a>
                            <a class="btn text-primary" wire:click="handleSms({{$leadItem->id}})"
                                data-bs-toggle="tooltip" title="{{__('translation.leads.send_sms')}}">
                                <i class="fa-solid fa-message"></i>
                            </a>
                            @if(!$leadItem->reminders->count())
                            <a class="btn text-primary" wire:click="handleReminder({{$leadItem['id']}})"
                                data-bs-toggle="tooltip" title="{{__('translation.leads.add_reminder')}}">
                                <i class="fa-solid fa-bell"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="100" class="text-center">
                            {{__("translation.no_data")}}
                        </td>
                    </tr>
                    @endforelse
                </table>
                {{ $leads->links() }}
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

    @include('livewire.leads.partials.lead-modal')
    @include('livewire.leads.partials.call-modal')
    @include('livewire.leads.partials.reminder-modal')

    @section("script")
    <!--jquery cdn-->
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>

    <!--select2 cdn-->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(document).ready(function () {
                $('.teachers').select2({
                    placeholder: "@lang('translation.leads.select_teacher')",
                    allowClear: true,
                });

                $('.boards').select2({
                    placeholder: "@lang('translation.leads.select_board')",
                    allowClear: true,
                });

                $('.causers').select2({
                    placeholder: "@lang('translation.leads.select_causer')",
                    allowClear: true,
                });
            });

            document.addEventListener('livewire:init', () => {
                Livewire.on('leadUpdated', (event) => {
                    window.notifyMessage("{!! __('translation.alert_messages.lead_updated') !!}", 'success')
                });
                Livewire.on('task-clicked', (event) => {
                    $('#leadModal').modal('show');
                });
                Livewire.on('handleCall', (event) => {
                    $('#callModal').modal('show');
                });
                Livewire.on('handleReminder', (event) => {
                    $('#reminderModal').modal('show');
                })
            });

    </script>
    <script>
        @if ($errors->any())
            window.notifyMessage("{{ $errors->first() }}", 'danger')
            @endif
    </script>
    <script>
        //set locale moment
        moment.locale('{{app()->getLocale() == "ru" ? "ru" : "uz-latn"}}');
        //Date range as a button
        $('#daterange-btn-debts').daterangepicker({
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
                eval($('#daterange-btn-debts').attr('data-livewire')).set('date', selected);
                $('#daterange-btn-debts span').html(selected);
            }
        );
    </script>
    @endsection
</div>
