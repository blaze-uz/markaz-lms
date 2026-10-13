@extends('layouts.master')
@section('title')
@lang('translation.settings')
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<form action="{{ route('admin.settings.menu-settings-update') }}" class="mb-3" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="menu" value="">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0 card-title">
                @lang('translation.menu_settings')
            </h4>
        </div>
        <div class="card-body" id="dynamic-menu">
            <p class="text-muted">@lang("translation.menu_settings_description")</p>
            <div id="menu-list" class="list-group nested-list nested-sortable">
                @foreach($menu_items as $menu)
                @include('admin.settings.menu-settings.menu-item', ['menu' => $menu])
                @endforeach
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <button class="btn btn-primary">
            @lang('translation.btn.save')
        </button>
    </div>
</form>
@endsection
@section('script')
<script src="{{ asset('assets/libs/sortablejs/Sortable.min.js') }}"></script>
<script src="{{ asset('assets/js/nestable.init.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
            const nestedSortables = document.querySelectorAll('.nested-sortable');
            for (let i = 0; i < nestedSortables.length; i++) {
                new Sortable(nestedSortables[i], {
                    group: 'nested',
                    animation: 150,
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                });
            }

            var bar = document.getElementById('menu-list');
            bar.addEventListener('mouseover', function () {
                updatePositions();
            });

            function updatePositions() {
                console.log('Updating positions');
                const menu = [];
                document.querySelectorAll('#menu-list > .list-group-item').forEach((item, index) => {
                    const id = item.getAttribute('data-id');
                    if (!id) {
                        console.error('Missing data-id attribute on item', item); // Error handling
                        return;
                    }
                    const children = [];
                    item.querySelectorAll('.nested-list > .list-group-item').forEach((child, childIndex) => {
                        const childId = child.getAttribute('data-id');
                        if (!childId) {
                            console.error('Missing data-id attribute on child', child); // Error handling
                            return;
                        }
                        children.push({
                            id: childId,
                            position: childIndex,
                        });
                    });
                    menu.push({
                        id,
                        position: index,
                        children,
                    });
                });

                document.querySelector('input[name="menu"]').value = JSON.stringify(menu);
                // Ensure updatePositions is called before form submission
                document.querySelector('form').addEventListener('submit', function (e) {
                    updatePositions();
                });
            }
        });
</script>
@endsection