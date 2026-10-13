@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
@endpush
<div class="card-body">
    @php
        $studentFormValue = fn (string $field) => old($field, $item->{$field} ?? '');
        $studentGender = old('gender', isset($item) ? $item->gender->value : 'male');
    @endphp
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <x-input :label="__('translation.students.name')" name="name" :placeholder="__('translation.students.name')"
                     class="firstCapitalInput" :value="$studentFormValue('name')" autocomplete="off"/>
        </div>
        <div class="col-md-6 col-sm-12">
            <x-input :label="__('translation.students.surname')" name="surname" class="firstCapitalInput"
                     :placeholder="__('translation.students.surname')" :value="$studentFormValue('surname')" autocomplete="off"/>
        </div>
        <div class="col-md-6 col-sm-12">
            @if(request()->routeIs('admin.students.create'))
                <livewire:student.phone-input-validation/>
                @error('phone')
                <div class="text-danger">* {{$message}}</div>
                @enderror
            @else
                <x-phone-input :label="__('translation.students.phone')" name="phone"
                               :placeholder="__('translation.students.phone')"
                               :value="$studentFormValue('phone')"/>
            @endif
        </div>
        <div class="col-md-6 col-sm-12">
            <x-phone-input :label="__('translation.students.phone2')" name="phone2"
                           :placeholder="__('translation.students.phone2')"
                           :value="$studentFormValue('phone2')"/>
        </div>
        <div class="col-md-6 col-sm-12">
            <x-input :label="__('translation.students.address')" name="address"
                     :placeholder="__('translation.students.address')" :value="$studentFormValue('address')" autocomplete="off"/>
        </div>
        <div class="col-md-6 col-sm-12">
            <x-phone-input :label="__('translation.students.parent_phone')" name="parent_phone"
                           :placeholder="__('translation.students.parent_phone')"
                           :value="$studentFormValue('parent_phone')"/>
        </div>
        <div class="col-md-6 col-sm-12 mb-3">
            <label for="">{{__("attr.gender")}}</label>
            <div class="d-flex justify-content-start gap-2">
                <div class="mr-3">
                    <input type="radio" class="btn-check" name="gender" id="primary-outlined" value="male"
                           @checked($studentGender === 'male')>
                    <label class="btn btn-outline-primary" for="primary-outlined">
                        <svg viewBox="0 0 24 24" width="1.2em" height="1.2em" class="w-5 h-4">
                            <path fill="currentColor"
                                  d="M9 11.75A1.25 1.25 0 0 0 7.75 13A1.25 1.25 0 0 0 9 14.25A1.25 1.25 0 0 0 10.25 13A1.25 1.25 0 0 0 9 11.75m6 0A1.25 1.25 0 0 0 13.75 13A1.25 1.25 0 0 0 15 14.25A1.25 1.25 0 0 0 16.25 13A1.25 1.25 0 0 0 15 11.75M12 2A10 10 0 0 0 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2m0 18c-4.41 0-8-3.59-8-8c0-.29 0-.58.05-.86c2.36-1.05 4.23-2.98 5.21-5.37a9.974 9.974 0 0 0 10.41 3.97c.21.71.33 1.47.33 2.26c0 4.41-3.59 8-8 8Z"></path>
                        </svg> {{__("attr.male")}}</label>
                </div>
                <div>
                    <input type="radio" class="btn-check" name="gender" id="danger-outlined" value="female"
                           @checked($studentGender === 'female')>
                    <label class="btn btn-outline-primary" for="danger-outlined">
                        <svg viewBox="0 0 24 24" width="1.2em" height="1.2em" class="w-5 h-4">
                            <path fill="currentColor"
                                  d="M13.75 13a1.25 1.25 0 1 1 2.5 0a1.25 1.25 0 0 1-2.5 0M22 12v10H2V12C2 6.5 6.5 2 12 2s10 4.5 10 10M4 12c0 4.41 3.59 8 8 8s8-3.59 8-8c0-.79-.12-1.55-.33-2.26A9.974 9.974 0 0 1 9.26 5.77c-.98 2.39-2.85 4.32-5.21 5.37c-.05.28-.05.57-.05.86m5 2.25a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5Z"></path>
                        </svg> {{__("attr.female")}}
                    </label>
                </div>
                @error('gender')
                <div class="text-danger">* {{$message}}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6 col-sm-12">
            <x-input :label="__('translation.students.birthday')" name="birthday" type="date"
                     :placeholder="__('translation.students.birthday')"
                     :value="$studentFormValue('birthday')"/>
        </div>
        <div class="col-md-6 col-sm-12">
            <x-text-area :label="__('translation.students.comment')" name="comment"
                         :placeholder="__('translation.students.comment')"
                         :value="$studentFormValue('comment')"/>
        </div>
        @if(!isset($item))
            <div class="col-md-6 col-sm-12">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="attachGroupSwitch">
                    <label class="form-check-label" for="attachGroupSwitch">
                        @lang("translation.students.attach_to_group")
                    </label>
                </div>
                <div id="groupSelectContainer" class="d-none">
                    <label for="groups">@lang('translation.groups.group')</label>
                    <select class="form-select group-select" id="groups" name="group_id">
                        <option value=""></option>
                        @foreach($groups as $group)
                            <option value="{{$group->id}}" @selected((string) old('group_id') === (string) $group->id)>{{$group->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
    </div>
    @push('custom-scripts')
        <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const attachGroupSwitch = document.getElementById('attachGroupSwitch');
                const groupSelectContainer = document.getElementById('groupSelectContainer');
                const groupSelect = document.getElementById('groups');
                attachGroupSwitch.addEventListener('change', function () {
                    if (this.checked) {
                        groupSelectContainer.classList.remove('d-none');
                    } else {
                        groupSelectContainer.classList.add('d-none');
                        groupSelect.value = '';
                    }
                });

                $('.group-select').select2({
                    placeholder: "@lang("translation.groups.choose_group")",
                });
            });
        </script>
    @endpush
</div>
