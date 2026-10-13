<div class="card-body">
    <x-input :label="__('translation.telegram.name')"
             name="name"
             :placeholder="__('translation.telegram.name')"
             :value="$item->name ?? old('name') ?? ''"
    />
    <x-input :label="__('translation.telegram.id')"
             name="telegram_id"
             :placeholder="__('translation.telegram.id')"
             :value="$item->telegram_id ?? old('telegram_id') ?? ''"
    />
</div>
