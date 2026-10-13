<div class="card-body">
    <x-input :label="__('translation.branches.branch')"
             name="name"
             :placeholder="__('translation.branches.branch')"
             :value="$item->name ?? old('name') ?? ''"
    />
    <x-input :label="__('translation.users.address')"
             name="address"
             :placeholder="__('translation.users.address')"
             :value="$item->address ?? old('address') ?? ''"
    />
    <x-phone-input :label="__('translation.users.phone')"
                   name="phone"
                   :placeholder="__('translation.users.phone')"
                   :value="$item->phone ?? old('phone') ?? ''"
    />
    <div class="mb-3">
        <label for="branch-status">
            @lang("translation.branches.status")
        </label>
        <select name="status" id="branch-status" class="form-control form-select">
            <option
                value="active" {{ (isset($item) && $item->status == 'active') || old('status') == 'active' ? 'selected' : '' }}>
                @lang("translation.branches.active")
            </option>
            <option
                value="inactive" {{ (isset($item) && $item->status == 'inactive') || old('status') == 'inactive' ? 'selected' : '' }}>
                @lang("translation.branches.inactive")
            </option>
        </select>
    </div>
</div>
