<div class="card card-height-100 mb-3">
    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    @endpush
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">@lang('translation.room_calendar')</h5>
        <div class="form-group me-3 w-25" wire:ignore>
            <select name="room_id" id="room"
                    data-control="select2"
                    wire:model="selectedRoomId"
                    class="form-select room-select form-control @error('room_id') is-invalid @enderror">
                <option value="">@lang("translation.choose_room")</option>
                @foreach($rooms as $room)
                    <option value="{{$room->id}}" @if($selectedRoomId == $room->id) selected @endif>
                        {{$room->name}}
                    </option>
                @endforeach
            </select>
            @error('room_id')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
    </div>
    <div class="card-body">
        <div style="position: relative; height: 100%; width: 100%;">
            <div style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; overflow-y: scroll">
                        <div class="table-responsive">
                            <table class="table">
                                @if($groupTimes)
                                    @foreach($groupTimes as $key => $groupTime)
                                        <tr>
                                            <th>{{ __("attr.".$key) }}</th>
                                            <td>
                                                @forelse($groupTime as $time)
                                                    <div class="d-flex justify-content-between align-items-center">
                                                            <span>{{ $time['start'] }} - {{ $time['end'] }} | {{$time['teacher']}} ({{$time['group']}})
                                                                <a href="{{route('admin.groups.edit', $time['group_id'])}}"
                                                                   data-bs-toggle="tooltip" title="{{__('attr.edit')}}">
                                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                                </a>
                                                            </span>
                                                    </div>
                                                @empty
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span>@lang('translation.no_class')</span>
                                                    </div>
                                                @endforelse
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="100" class="text-center">
                                            <x-no-result/>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>

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
    @push('custom-scripts')
        <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function () {
                $('.room-select').select2({
                    placeholder: "@lang("translation.choose_room")",
                });

                $('.room-select').on('select2:select', function (e) {
                    let value = $(this).val();
                    Livewire.dispatch('select-room', {room_id: value});
                });
            });
        </script>
    @endpush
</div>
