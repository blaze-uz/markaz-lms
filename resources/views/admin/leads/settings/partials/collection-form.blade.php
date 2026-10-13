<x-input
    name="name"
    label="{{__('translation.leads.collection_name')}}"
    :value="$item->name ?? old('name') ?? ''"
/>
