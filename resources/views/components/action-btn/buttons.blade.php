<ul class="list-inline d-inline-block hstack gap-2 mb-0 mx-auto">
    @if($showable)
        <x-action-btn.show-btn :url="$showUrl"></x-action-btn.show-btn>
    @endif
    @if($editable)
        <x-action-btn.edit-btn :url="$editUrl"></x-action-btn.edit-btn>
    @endif
    @if($deletable)
        <x-action-btn.delete-btn
                :id="$id"
                :modal-id="$deleteModalId"
        ></x-action-btn.delete-btn>
    @endif
</ul>
