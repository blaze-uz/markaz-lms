@props(['url', 'modalId' => 'deleteRecordModal'])

<div class="modal fade zoomIn" id="{{ $modalId }}" tabindex="-1" aria-labelledby="deleteRecordLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="btn-close"></button>
            </div>
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                           colors="primary:#405189,secondary:#f06548" style="width:90px;height:90px">
                </lord-icon>
                <div class="mt-4 text-center">
                    <h4 class="fs-semibold">@lang("translation.Are you sure you want to delete?")</h4>
                    <p class="text-muted fs-14 mb-4 pt-1">@lang("translation.Deleting will remove all of your information from our database.")</p>
                    <div class="hstack gap-2 justify-content-center remove">
                        <button class="btn btn-link link-success fw-medium text-decoration-none" data-bs-dismiss="modal"
                                id="deleteRecord-close">
                            <i class="ri-close-line me-1 align-middle"></i>
                            @lang("translation.Close")
                        </button>
                        <input type="hidden" id="deletingItemInputBase" name="ids[]" value="">
                        <form action="{{ $url }}" method="post" id="deleteItemsForm">
                            @csrf
                            @method('DELETE')
                            <div class="d-none" id="deletingItemsBox"></div>
                            <button type="submit" class="btn btn-danger" id="delete-record">
                                @lang("translation.Yes, Delete!!")
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
