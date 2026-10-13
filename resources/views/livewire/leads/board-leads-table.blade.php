<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <div class="card-title">
                @lang("translation.leads.leads")
            </div>
            <div class="d-flex gap-3 align-items-center">
                @if(count($selectedLeads) > 0)
                    <button class="btn btn-warning" wire:click="handleSendSms">
                        <i class="fa-solid fa-envelope"></i>
                        @lang("translation.sms.send")
                    </button>
                @endif
                <div class="position-relative">
                    <x-search parent-class="m-0" wire:model.live.debounce.300ms="search"/>
                    <div wire:loading.delay wire:target="search" class="position-absolute top-50 end-0 translate-middle-y me-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault"
                                       wire:model.live="selectAll"
                                       wire:change="handleSelectAll"
                                       @if(count($selectedLeads) === count($items)) checked @endif />
                            </div>
                        </th>
                        <th>ID</th>
                        <th>@lang("translation.leads.name")</th>
                        <th>@lang("translation.leads.phone")</th>
                        <th>@lang("translation.leads.chosen_time")</th>
                        <th>@lang("translation.leads.teacher")</th>
                        {{--                        <th>@lang("translation.leads.attend")</th>--}}
                        <th>@lang("translation.leads.calls")</th>
                        <th>@lang("translation.leads.date")</th>
                        <th>@lang("translation.actions")</th>
                    </tr>
                    <tbody wire:loading.class.delay="opacity-50">
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="lead_{{ $item->id }}"
                                           wire:model.live="selectedLeads" value="{{ $item->id }}"/>
                                </div>
                            </td>
                            <td>{{$item->id}}</td>
                            <td>{{$item->name}}</td>
                            <td class="text-nowrap">
                                <a href="tel:{{$item->phone}}">{{$item->phone}}</a>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success">
                                    {{ $item->chosen_days ? __('translation.leads.'.$item->chosen_days) : __('translation.leads.other')}}
                                </span><br>
                                <span class="badge bg-success-subtle text-success">
                                    {{$item->chosen_time ? dateFormat($item->chosen_time, 'H:i') : '-'}}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                @foreach($item->teachers as $teacher)
                                    <span>{{$teacher->name}}</span>
                                @endforeach
                            </td>
                            {{--                            <td>--}}
                            {{--                                <div class="form-check form-switch">--}}
                            {{--                                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault"--}}
                            {{--                                           @if($item->attend) checked @endif--}}
                            {{--                                           wire:change="handleAttend({{$item->id}})"/>--}}
                            {{--                                </div>--}}
                            {{--                            </td>--}}
                            <td>
                                <span class="text-danger">{{__("translation.leads.declined")}}: {{$item->calls()->where('status', 'declined')->count()}}</span><br>
                                <span class="text-success">{{__("translation.leads.answered")}}: {{$item->calls()->where('status', 'answered')->count()}}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column cursor-pointer" data-bs-toggle="tooltip"
                                     title="{{dateFormat($item->created_at, 'd.m.Y H:i')}}" wire:ignore.self>
                                    <span>{{dateFormat($item->created_at)}}</span>
                                    <small class="text-muted">{{$item->causer?->name ?? 'Causer not found'}}</small>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <a class="btn text-primary" data-bs-toggle="tooltip"
                                   wire:click="clickTask({{$item->id}})"
                                   title="{{__("translation.leads.show")}}">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a class="btn text-primary" wire:click="handleCall({{$item->id}})"
                                   data-bs-toggle="tooltip" title="{{__('translation.leads.call')}}">
                                    <i class="fa-solid fa-phone"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                @lang("translation.no_data")
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0">@lang("translation.per_page")</label>
                        <select wire:model.live="perPage" class="form-select form-select-sm w-auto">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>


    @include('livewire.leads.partials.lead-modal')
    @include('livewire.leads.partials.call-modal')

    @section("script")
        <!--jquery cdn-->
        <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>

        <!--select2 cdn-->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#teachers').select2({
                    placeholder: "@lang('translation.leads.select_teacher')",
                    allowClear: true,
                    multiple: true,
                    closeOnSelect: false,
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
                })
                Livewire.on('task-removed', (event) => {
                    $('#leadModal').modal('hide');
                });
            });

        </script>
        <script>
            @if ($errors->any())
            window.notifyMessage("{{ $errors->first() }}", 'danger')
            @endif
        </script>
    @endsection
</div>
