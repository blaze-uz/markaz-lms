@section("css")
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
@endsection
<div class="card-body">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <x-input :label="__('translation.users.full_name')"
                     name="name"
                     :placeholder="__('translation.users.full_name')"
                     :value="$item->name ?? old('name') ?? ''"
            />
        </div>
        <div class="col-md-6 col-sm-12">
            <x-input :label="__('translation.login')"
                     name="email"
                     :placeholder="__('translation.login')"
                     :value="$item->email ?? old('email') ?? ''"
            />
        </div>
        <div class="col-md-6 col-sm-12">
            <x-input :label="__('translation.users.birthday')"
                     name="birthday"
                     type="date"
                     :placeholder="__('translation.users.birthday')"
                     :value="$item->birthday ?? old('birthday') ?? ''"
            />
        </div>
        <div class="col-md-6 col-sm-12">
            <x-input :label="__('translation.users.password')"
                     name="password"
                     type="password"
                     :placeholder="__('translation.users.password')"
            />
        </div>
        <div class="col-md-6 col-sm-12">
            <x-phone-input :label="__('translation.users.phone')"
                           name="phone"
                           :placeholder="__('translation.users.phone')"
                           :value="$item->phone ?? old('phone') ?? ''"
            />
        </div>
        <div class="col-md-6 col-sm-12">
            <div class="mb-3 form-group">
                <label for="role">@lang("translation.users.role")</label>
                <select name="role" id="role" class="form-control @error('role') is-invalid @enderror">
                    <option value="">@lang("translation.users.select_role")</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}"
                                @if(isset($item) && $item->hasRole($role->name)) selected @endif>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6 col-sm-12">
            <x-input :label="__('translation.users.percentage')"
                     name="percentage"
                     type="number"
                     attribute="min=0 max=100"
                     :placeholder="__('translation.users.percentage')"
                     :value="$item->percentage ?? 0"
            />
        </div>
        <div class="col-md-6 col-sm-12">
            <x-input :label="__('translation.users.stars')"
                     name="stars"
                     type="number"
                     attribute="min=0"
                     :placeholder="__('translation.users.stars')"
                     :value="$item->stars ?? 0"
            />
        </div>
        <div class="col-md-6 col-sm-12">
            <label for="inputGroupFile02">@lang("translation.users.image")</label><br>
            <div class="input-group">
                <input type="file" class="form-control" id="inputGroupFile02" name="avatar">
                <label class="input-group-text" for="inputGroupFile02">@lang("attr.upload")</label>
            </div>
            @error('avatar')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 col-sm-12">
            <label for="branch">
                @lang("translation.users.choose_branch")
            </label>
            <select name="branches[]" id="branch" class="form-control" multiple>
                @foreach($branches as $key => $branch)
                    <option value="{{ $branch->id }}"
                            @if(!isset($item) && $key == 0) selected @endif
                            @if(isset($item) && $item->branches->contains($branch->id)) selected @endif>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>
@section("script")
    <!--select2 cdn-->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#branch').select2({
                placeholder: "@lang('translation.users.choose_branch')",
                allowClear: true,
                multiple: true,
                closeOnSelect: false,
            });
        });
    </script>
@endsection
