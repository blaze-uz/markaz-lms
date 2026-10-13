<!-- removeNotificationModal -->
<div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    id="NotificationModalbtn-close"></button>
            </div>
            <div class="modal-body">
                <div class="mt-2 text-center">
                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                        colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                    <div class="pt-2 mx-4 mt-4 fs-15 mx-sm-5">
                        <h4>Are you sure ?</h4>
                        <p class="mx-4 mb-0 text-muted">Are you sure you want to remove this Notification ?</p>
                    </div>
                </div>
                <div class="gap-2 mt-4 mb-2 d-flex justify-content-center">
                    <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete It!</button>
                </div>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<!-- Search Modal -->
{{-- <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">
                    @lang("translation.Search")
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.students.index') }}">
                <div class="modal-body">
                    <x-input
                        label="{{__('translation.students.name')}} {{__('attr.or')}} {{__('translation.students.surname')}}"
                        type="text" name="search"
                        placeholder="{{__('translation.students.name')}} {{__('attr.or')}} {{__('translation.students.surname')}}"
                        :value="request()->search ?? old('search') ?? ''" />
                    <x-phone-input label="{{__('translation.students.phone')}}" name="phone"
                        placeholder="{{__('translation.students.phone')}}"
                        :value="request()->phone ?? old('phone') ?? ''" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        @lang("translation.Close")
                    </button>
                    <button type="submit" class="btn btn-primary">
                        @lang("translation.search")
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> --}}

<livewire:layout.branch-modal />