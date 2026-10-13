<div>
    <div class="card">
        <div class="card-body">
            <x-input :label="__('translation.roles.name')"
                     :attribute="(isset($item) && ($item->name == 'Admin' || $item->name == 'Teacher')) ? 'disabled' : ''"
                     name="name"
                     :placeholder="__('translation.roles.name')"
                     :value="$item->name ?? old('name') ?? ''"
            />
        </div>
    </div>
    @if(isset($item)  && ($item->name == 'Admin' || $item->name == 'Teacher'))
        <input type="hidden" name="name" value="{{ $item->name ?? old('name') }}">
    @endif
    @if($permissions->count() > 0)
        <div class="row">
            @foreach($permissions as $key => $groupedPermissions)
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkAll{{$key}}">
                                    <label class="form-check-label" for="checkAll{{$key}}">
                                        {{__('permissions.headers.check_all')}}
                                    </label>
                                </div>
                                <span class="fw-medium">
                                    {{__('permissions.headers.'.$key)}}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach($groupedPermissions as $permission)
                                <div class="border p-2">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                               id="formCheck{{$permission->id}}"
                                               name="permissions[]"
                                               value="{{$permission->id}}"
                                               @if(isset($item) && $item->hasPermissionTo($permission->name)) checked @endif>
                                        <label class="form-check-label" for="formCheck{{$permission->id}}">
                                            {{__('permissions.'.$permission->name)}}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    @push('custom-scripts')
        <script>
            document.querySelectorAll('input[id^="checkAll"]').forEach(function (checkAllBox) {
                checkAllBox.addEventListener('change', function (e) {
                    var cardBody = e.target.closest('.card').querySelector('.card-body');
                    var checkboxes = cardBody.querySelectorAll('input[type="checkbox"]');
                    for (var i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = e.target.checked;
                    }
                });

                var cardBody = checkAllBox.closest('.card').querySelector('.card-body');
                var checkboxes = cardBody.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        var allChecked = Array.from(checkboxes).every(c => c.checked);
                        checkAllBox.checked = allChecked;
                    });
                });

                // Check the state of all checkboxes on page load
                var allCheckedOnLoad = Array.from(checkboxes).every(c => c.checked);
                checkAllBox.checked = allCheckedOnLoad;
            });
        </script>
    @endpush
</div>