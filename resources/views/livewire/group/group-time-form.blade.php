<div>
    <div class="card rounded-4">
        <div class="card-body">
            <p class="text-muted">@lang('translation.groups.group_times')</p>
            @if (count($errors))
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors as $error)
                        {{ $error }} <br>
                    @endforeach
                </div>
            @endif
            {{-- checkboxes --}}
            <div class="mb-3 d-flex">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked"
                        wire:model.live="isOddDay">
                    <label class="form-check-label" for="flexSwitchCheckChecked">
                        @lang('translation.leads.odd')
                    </label>
                </div>
                <div class="mx-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked2"
                        wire:model.live="isEvenDay">
                    <label class="form-check-label" for="flexSwitchCheckChecked2">
                        @lang('translation.leads.even')
                    </label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked3"
                        wire:model.live="isSunday">
                    <label class="form-check-label" for="flexSwitchCheckChecked3">
                        @lang('attr.sunday')
                    </label>
                </div>
            </div>
            {{-- days --}}
            <div>
                @if ($isOddDay)
                    <div class="row">
                        <div class="col-md-5 col-sm-12">
                            <x-no-label-input name="monday" attribute="disabled" :placeholder="__('attr.monday')" />
                        </div>
                        <div class="col-md-7 col-sm-12">
                            <div class="row">
                                <!-- Desktop: all in one row, Mobile: times in one row, room in next row -->
                                <div class="d-none d-md-flex col-12">
                                    <div class="col-4 px-1">
                                        <x-no-label-input name="monday[start]" wire:model="monday.start" type="time"
                                            :value="isset($group)
                                                ? $group->groupTimes?->where('name', 'monday')->value('start') ?? ''
                                                : ''" />
                                    </div>
                                    <div class="col-4 px-1">
                                        <x-no-label-input name="monday[end]" wire:model="monday.end"
                                            wire:change="clearSelectedRoom('monday')" type="time"
                                            :value="isset($group)
                                                ? $group->groupTimes?->where('name', 'monday')->value('end') ?? ''
                                                : ''" />
                                    </div>
                                    <div class="col-4 px-1">
                                        <div class="mb-3">
                                            <select name="monday[room_id]" class="form-control form-select"
                                                wire:model="monday.room_id"
                                                wire:change="checkRoom('monday', $event.target.value)">
                                                <option value="">@lang('translation.groups.select_room')</option>
                                                @foreach ($rooms as $room)
                                                    <option value="{{ $room->id }}"
                                                        @if (isset($group) && $group->groupTimes?->where('name', 'monday')->value('room_id') == $room->id) selected @endif>
                                                        {{ $room->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- Mobile: times in one row, room in next row -->
                                <div class="d-md-none col-12">
                                    <div class="row">
                                        <div class="col-6 px-1">
                                            <x-no-label-input name="monday[start]" wire:model="monday.start"
                                                type="time" :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'monday')->value('start') ?? ''
                                                    : ''" />
                                        </div>
                                        <div class="col-6 px-1">
                                            <x-no-label-input name="monday[end]" wire:model="monday.end"
                                                wire:change="clearSelectedRoom('monday')" type="time"
                                                :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'monday')->value('end') ?? ''
                                                    : ''" />
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12 px-1">
                                            <div class="mb-3">
                                                <select name="monday[room_id]" class="form-control form-select"
                                                    wire:model="monday.room_id"
                                                    wire:change="checkRoom('monday', $event.target.value)">
                                                    <option value="">@lang('translation.groups.select_room')</option>
                                                    @foreach ($rooms as $room)
                                                        <option value="{{ $room->id }}"
                                                            @if (isset($group) && $group->groupTimes?->where('name', 'monday')->value('room_id') == $room->id) selected @endif>
                                                            {{ $room->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            @if (count($monday['errors']))
                                <div class="alert alert-danger" role="alert">
                                    @foreach ($monday['errors'] as $error)
                                        {{ $error }} <br>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                @if ($isEvenDay)
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-5 col-sm-12">
                                <x-no-label-input name="tuesday" attribute="disabled" :placeholder="__('attr.tuesday')" />
                            </div>
                            <div class="col-md-7 col-sm-12">
                                <div class="row">
                                    <!-- Desktop: all in one row, Mobile: times in one row, room in next row -->
                                    <div class="d-none d-md-flex col-12">
                                        <div class="col-4 px-1">
                                            <x-no-label-input name="tuesday[start]" wire:model="tuesday.start"
                                                type="time" :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'tuesday')->value('start') ??
                                                        ''
                                                    : ''" />
                                        </div>
                                        <div class="col-4 px-1">
                                            <x-no-label-input name="tuesday[end]" wire:model="tuesday.end"
                                                wire:change="clearSelectedRoom('tuesday')" type="time"
                                                :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'tuesday')->value('end') ?? ''
                                                    : ''" />
                                        </div>
                                        <div class="col-4 px-1">
                                            <div class="mb-3">
                                                <select name="tuesday[room_id]" class="form-control form-select"
                                                    wire:model="tuesday.room_id"
                                                    wire:change="checkRoom('tuesday', $event.target.value)">
                                                    <option value="">@lang('translation.groups.select_room')</option>
                                                    @foreach ($rooms as $room)
                                                        <option value="{{ $room->id }}"
                                                            @if (isset($group) && $group->groupTimes?->where('name', 'tuesday')->value('room_id') == $room->id) selected @endif>
                                                            {{ $room->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-md-none col-12">
                                        <div class="row">
                                            <div class="col-6 px-1">
                                                <x-no-label-input name="tuesday[start]" wire:model="tuesday.start"
                                                    type="time" :value="isset($group)
                                                        ? $group->groupTimes
                                                                ?->where('name', 'tuesday')
                                                                ->value('start') ?? ''
                                                        : ''" />
                                            </div>
                                            <div class="col-6 px-1">
                                                <x-no-label-input name="tuesday[end]" wire:model="tuesday.end"
                                                    wire:change="clearSelectedRoom('tuesday')" type="time"
                                                    :value="isset($group)
                                                        ? $group->groupTimes?->where('name', 'tuesday')->value('end') ??
                                                            ''
                                                        : ''" />
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12 px-1">
                                                <div class="mb-3">
                                                    <select name="tuesday[room_id]" class="form-control form-select"
                                                        wire:model="tuesday.room_id"
                                                        wire:change="checkRoom('tuesday', $event.target.value)">
                                                        <option value="">@lang('translation.groups.select_room')</option>
                                                        @foreach ($rooms as $room)
                                                            <option value="{{ $room->id }}"
                                                                @if (isset($group) && $group->groupTimes?->where('name', 'tuesday')->value('room_id') == $room->id) selected @endif>
                                                                {{ $room->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                @if (count($tuesday['errors']))
                                    <div class="alert alert-danger" role="alert">
                                        @foreach ($tuesday['errors'] as $error)
                                            {{ $error }} <br>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                @if ($isOddDay)
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-5 col-sm-12">
                                <x-no-label-input name="wednesday" attribute="disabled" :placeholder="__('attr.wednesday')" />
                            </div>
                            <div class="col-md-7 col-sm-12">
                                <div class="row">
                                    <div class="d-none d-md-flex col-12">
                                        <div class="col-4 px-1">
                                            <x-no-label-input name="wednesday[start]" wire:model="wednesday.start"
                                                type="time" :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'wednesday')->value('start') ??
                                                        ''
                                                    : ''" />
                                        </div>
                                        <div class="col-4 px-1">
                                            <x-no-label-input name="wednesday[end]" wire:model="wednesday.end"
                                                wire:change="clearSelectedRoom('wednesday')" type="time"
                                                :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'wednesday')->value('end') ??
                                                        ''
                                                    : ''" />
                                        </div>
                                        <div class="col-4 px-1">
                                            <div class="mb-3">
                                                <select name="wednesday[room_id]" class="form-control form-select"
                                                    wire:model="wednesday.room_id"
                                                    wire:change="checkRoom('wednesday', $event.target.value)">
                                                    <option value="">@lang('translation.groups.select_room')</option>
                                                    @foreach ($rooms as $room)
                                                        <option value="{{ $room->id }}"
                                                            @if (isset($group) && $group->groupTimes?->where('name', 'wednesday')->value('room_id') == $room->id) selected @endif>
                                                            {{ $room->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-md-none col-12">
                                        <div class="row">
                                            <div class="col-6 px-1">
                                                <x-no-label-input name="wednesday[start]" wire:model="wednesday.start"
                                                    type="time" :value="isset($group)
                                                        ? $group->groupTimes
                                                                ?->where('name', 'wednesday')
                                                                ->value('start') ?? ''
                                                        : ''" />
                                            </div>
                                            <div class="col-6 px-1">
                                                <x-no-label-input name="wednesday[end]" wire:model="wednesday.end"
                                                    wire:change="clearSelectedRoom('wednesday')" type="time"
                                                    :value="isset($group)
                                                        ? $group->groupTimes
                                                                ?->where('name', 'wednesday')
                                                                ->value('end') ?? ''
                                                        : ''" />
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12 px-1">
                                                <div class="mb-3">
                                                    <select name="wednesday[room_id]" class="form-control form-select"
                                                        wire:model="wednesday.room_id"
                                                        wire:change="checkRoom('wednesday', $event.target.value)">
                                                        <option value="">@lang('translation.groups.select_room')</option>
                                                        @foreach ($rooms as $room)
                                                            <option value="{{ $room->id }}"
                                                                @if (isset($group) && $group->groupTimes?->where('name', 'wednesday')->value('room_id') == $room->id) selected @endif>
                                                                {{ $room->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                @if (count($wednesday['errors']))
                                    <div class="alert alert-danger" role="alert">
                                        @foreach ($wednesday['errors'] as $error)
                                            {{ $error }} <br>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                @if ($isEvenDay)
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-5 col-sm-12">
                                <x-no-label-input name="thursday" attribute="disabled" :placeholder="__('attr.thursday')" />
                            </div>
                            <div class="col-md-7 col-sm-12">
                                <div class="row">
                                    <div class="d-none d-md-flex col-12">
                                        <div class="col-4 px-1">
                                            <x-no-label-input name="thursday[start]" wire:model="thursday.start"
                                                type="time" :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'thursday')->value('start') ??
                                                        ''
                                                    : ''" />
                                        </div>
                                        <div class="col-4 px-1">
                                            <x-no-label-input name="thursday[end]" wire:model="thursday.end"
                                                wire:change="clearSelectedRoom('thursday')" type="time"
                                                :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'thursday')->value('end') ?? ''
                                                    : ''" />
                                        </div>
                                        <div class="col-4 px-1">
                                            <div class="mb-3">
                                                <select name="thursday[room_id]" class="form-control form-select"
                                                    wire:model="thursday.room_id"
                                                    wire:change="checkRoom('thursday', $event.target.value)">
                                                    <option value="">@lang('translation.groups.select_room')</option>
                                                    @foreach ($rooms as $room)
                                                        <option value="{{ $room->id }}"
                                                            @if (isset($group) && $group->groupTimes?->where('name', 'thursday')->value('room_id') == $room->id) selected @endif>
                                                            {{ $room->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-md-none col-12">
                                        <div class="row">
                                            <div class="col-6 px-1">
                                                <x-no-label-input name="thursday[start]" wire:model="thursday.start"
                                                    type="time" :value="isset($group)
                                                        ? $group->groupTimes
                                                                ?->where('name', 'thursday')
                                                                ->value('start') ?? ''
                                                        : ''" />
                                            </div>
                                            <div class="col-6 px-1">
                                                <x-no-label-input name="thursday[end]" wire:model="thursday.end"
                                                    wire:change="clearSelectedRoom('thursday')" type="time"
                                                    :value="isset($group)
                                                        ? $group->groupTimes
                                                                ?->where('name', 'thursday')
                                                                ->value('end') ?? ''
                                                        : ''" />
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12 px-1">
                                                <div class="mb-3">
                                                    <select name="thursday[room_id]" class="form-control form-select"
                                                        wire:model="thursday.room_id"
                                                        wire:change="checkRoom('thursday', $event.target.value)">
                                                        <option value="">@lang('translation.groups.select_room')</option>
                                                        @foreach ($rooms as $room)
                                                            <option value="{{ $room->id }}"
                                                                @if (isset($group) && $group->groupTimes?->where('name', 'thursday')->value('room_id') == $room->id) selected @endif>
                                                                {{ $room->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                @if (count($thursday['errors']))
                                    <div class="alert alert-danger" role="alert">
                                        @foreach ($thursday['errors'] as $error)
                                            {{ $error }} <br>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                @if ($isOddDay)
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-5 col-sm-12">
                                <x-no-label-input name="friday" attribute="disabled" :placeholder="__('attr.friday')" />
                            </div>
                            <div class="col-md-7 col-sm-12">
                                <div class="row">
                                    <div class="d-none d-md-flex col-12">
                                        <div class="col-4 px-1">
                                            <x-no-label-input name="friday[start]" wire:model="friday.start"
                                                type="time" :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'friday')->value('start') ?? ''
                                                    : ''" />
                                        </div>
                                        <div class="col-4 px-1">
                                            <x-no-label-input name="friday[end]" wire:model="friday.end"
                                                wire:change="clearSelectedRoom('friday')" type="time"
                                                :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'friday')->value('end') ?? ''
                                                    : ''" />
                                        </div>
                                        <div class="col-4 px-1">
                                            <div class="mb-3">
                                                <select name="friday[room_id]" class="form-control form-select"
                                                    wire:model="friday.room_id"
                                                    wire:change="checkRoom('friday', $event.target.value)">
                                                    <option value="">@lang('translation.groups.select_room')</option>
                                                    @foreach ($rooms as $room)
                                                        <option value="{{ $room->id }}"
                                                            @if (isset($group) && $group->groupTimes?->where('name', 'friday')->value('room_id') == $room->id) selected @endif>
                                                            {{ $room->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-md-none col-12">
                                        <div class="row">
                                            <div class="col-6 px-1">
                                                <x-no-label-input name="friday[start]" wire:model="friday.start"
                                                    type="time" :value="isset($group)
                                                        ? $group->groupTimes
                                                                ?->where('name', 'friday')
                                                                ->value('start') ?? ''
                                                        : ''" />
                                            </div>
                                            <div class="col-6 px-1">
                                                <x-no-label-input name="friday[end]" wire:model="friday.end"
                                                    wire:change="clearSelectedRoom('friday')" type="time"
                                                    :value="isset($group)
                                                        ? $group->groupTimes?->where('name', 'friday')->value('end') ??
                                                            ''
                                                        : ''" />
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12 px-1">
                                                <div class="mb-3">
                                                    <select name="friday[room_id]" class="form-control form-select"
                                                        wire:model="friday.room_id"
                                                        wire:change="checkRoom('friday', $event.target.value)">
                                                        <option value="">@lang('translation.groups.select_room')</option>
                                                        @foreach ($rooms as $room)
                                                            <option value="{{ $room->id }}"
                                                                @if (isset($group) && $group->groupTimes?->where('name', 'friday')->value('room_id') == $room->id) selected @endif>
                                                                {{ $room->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                @if (count($friday['errors']))
                                    <div class="alert alert-danger" role="alert">
                                        @foreach ($friday['errors'] as $error)
                                            {{ $error }} <br>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                @if ($isEvenDay)
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-5 col-sm-12">
                                <x-no-label-input name="saturday" attribute="disabled" :placeholder="__('attr.saturday')" />
                            </div>
                            <div class="col-md-7 col-sm-12">
                                <div class="row">
                                    <div class="d-none d-md-flex col-12">
                                        <div class="col-4 px-1">
                                            <x-no-label-input name="saturday[start]" wire:model="saturday.start"
                                                type="time" :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'saturday')->value('start') ??
                                                        ''
                                                    : ''" />
                                        </div>
                                        <div class="col-4 px-1">
                                            <x-no-label-input name="saturday[end]" wire:model="saturday.end"
                                                wire:change="clearSelectedRoom('saturday')" type="time"
                                                :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'saturday')->value('end') ?? ''
                                                    : ''" />
                                        </div>
                                        <div class="col-4 px-1">
                                            <div class="mb-3">
                                                <select name="saturday[room_id]" class="form-control form-select"
                                                    wire:model="saturday.room_id"
                                                    wire:change="checkRoom('saturday', $event.target.value)">
                                                    <option value="">@lang('translation.groups.select_room')</option>
                                                    @foreach ($rooms as $room)
                                                        <option value="{{ $room->id }}"
                                                            @if (isset($group) && $group->groupTimes?->where('name', 'saturday')->value('room_id') == $room->id) selected @endif>
                                                            {{ $room->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-md-none col-12">
                                        <div class="row">
                                            <div class="col-6 px-1">
                                                <x-no-label-input name="saturday[start]" wire:model="saturday.start"
                                                    type="time" :value="isset($group)
                                                        ? $group->groupTimes
                                                                ?->where('name', 'saturday')
                                                                ->value('start') ?? ''
                                                        : ''" />
                                            </div>
                                            <div class="col-6 px-1">
                                                <x-no-label-input name="saturday[end]" wire:model="saturday.end"
                                                    wire:change="clearSelectedRoom('saturday')" type="time"
                                                    :value="isset($group)
                                                        ? $group->groupTimes
                                                                ?->where('name', 'saturday')
                                                                ->value('end') ?? ''
                                                        : ''" />
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12 px-1">
                                                <div class="mb-3">
                                                    <select name="saturday[room_id]" class="form-control form-select"
                                                        wire:model="saturday.room_id"
                                                        wire:change="checkRoom('saturday', $event.target.value)">
                                                        <option value="">@lang('translation.groups.select_room')</option>
                                                        @foreach ($rooms as $room)
                                                            <option value="{{ $room->id }}"
                                                                @if (isset($group) && $group->groupTimes?->where('name', 'saturday')->value('room_id') == $room->id) selected @endif>
                                                                {{ $room->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                @if (count($saturday['errors']))
                                    <div class="alert alert-danger" role="alert">
                                        @foreach ($saturday['errors'] as $error)
                                            {{ $error }} <br>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            @if ($isSunday)
                <div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-5 col-sm-12">
                                <x-no-label-input name="sunday" attribute="disabled" :placeholder="__('attr.sunday')" />
                            </div>
                            <div class="col-md-7 col-sm-12">
                                <div class="row">
                                    <div class="d-none d-md-flex col-12">
                                        <div class="col-4 px-1">
                                            <x-no-label-input name="sunday[start]" wire:model="sunday.start"
                                                type="time" :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'sunday')->value('start') ?? ''
                                                    : ''" />
                                        </div>
                                        <div class="col-4 px-1">
                                            <x-no-label-input name="sunday[end]" wire:model="sunday.end"
                                                wire:change="clearSelectedRoom('sunday')" type="time"
                                                :value="isset($group)
                                                    ? $group->groupTimes?->where('name', 'sunday')->value('end') ?? ''
                                                    : ''" />
                                        </div>
                                        <div class="col-4 px-1">
                                            <div class="mb-3">
                                                <select name="sunday[room_id]" class="form-control form-select"
                                                    wire:model="sunday.room_id"
                                                    wire:change="checkRoom('sunday', $event.target.value)">
                                                    <option value="">@lang('translation.groups.select_room')</option>
                                                    @foreach ($rooms as $room)
                                                        <option value="{{ $room->id }}"
                                                            @if (isset($group) && $group->groupTimes?->where('name', 'sunday')->value('room_id') == $room->id) selected @endif>
                                                            {{ $room->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-md-none col-12">
                                        <div class="row">
                                            <div class="col-6 px-1">
                                                <x-no-label-input name="sunday[start]" wire:model="sunday.start"
                                                    type="time" :value="isset($group)
                                                        ? $group->groupTimes
                                                                ?->where('name', 'sunday')
                                                                ->value('start') ?? ''
                                                        : ''" />
                                            </div>
                                            <div class="col-6 px-1">
                                                <x-no-label-input name="sunday[end]" wire:model="sunday.end"
                                                    wire:change="clearSelectedRoom('sunday')" type="time"
                                                    :value="isset($group)
                                                        ? $group->groupTimes?->where('name', 'sunday')->value('end') ??
                                                            ''
                                                        : ''" />
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12 px-1">
                                                <div class="mb-3">
                                                    <select name="sunday[room_id]" class="form-control form-select"
                                                        wire:model="sunday.room_id"
                                                        wire:change="checkRoom('sunday', $event.target.value)">
                                                        <option value="">@lang('translation.groups.select_room')</option>
                                                        @foreach ($rooms as $room)
                                                            <option value="{{ $room->id }}"
                                                                @if (isset($group) && $group->groupTimes?->where('name', 'sunday')->value('room_id') == $room->id) selected @endif>
                                                                {{ $room->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                @if (count($sunday['errors']))
                                    <div class="alert alert-danger" role="alert">
                                        @foreach ($sunday['errors'] as $error)
                                            {{ $error }} <br>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="card-preloader d-none" wire:target="checkRoom" wire:loading.class.remove="d-none">
                <div class="card-status">
                    <div class="spinner-border text-success">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div><!-- end card-body -->
    </div>

</div>
@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('change', function(e) {
                if (e.target.name && e.target.name.match(/\[start\]$/)) {
                    const startTime = e.target.value;
                    if (!startTime) {
                        return;
                    }

                    try {
                        const [hours, minutes] = startTime.split(':').map(Number);

                        // Calculate end time directly without date object to avoid timezone issues
                        let endHours = hours + 2;
                        let endMinutes = minutes;

                        // Handle day overflow (e.g., 23:00 + 2 hours = 01:00 next day)
                        if (endHours >= 24) {
                            endHours = endHours - 24;
                        }

                        // Format as HH:MM with zero padding
                        const endTime =
                            `${endHours.toString().padStart(2, '0')}:${endMinutes.toString().padStart(2, '0')}`;

                        // Extract day name from input name (e.g., "monday[start]" -> "monday")
                        const dayName = e.target.name.replace('[start]', '');

                        // Find and update the corresponding end time input
                        const endTimeInput = document.querySelector(`input[name="${dayName}[end]"]`);

                        if (endTimeInput) {
                            endTimeInput.value = endTime;

                            endTimeInput.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                        } else {}
                    } catch (error) {}
                }
            });
        });
    </script>
@endpush
