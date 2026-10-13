@php
    $route = route('admin.users.create');
@endphp

<a type="button" class="btn btn-success add-btn" href="{{ $route }}">
    <i class="ri-add-line align-bottom me-1"></i>
    @lang("translation.users.add")
</a>
