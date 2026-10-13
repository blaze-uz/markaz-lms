<div class="card-body">
    <x-input :label="__('translation.permissions.name')"
             name="name"
             :placeholder="__('translation.permissions.name')"
             :value="$item->name ?? old('name') ?? ''"
    />
</div>
