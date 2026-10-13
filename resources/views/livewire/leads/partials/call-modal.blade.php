<!-- Lead Modal -->
<div wire:ignore.self class="modal fade" id="callModal" tabindex="-1" aria-labelledby="callModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="callModalLabel">{{$selectedLead?->name}}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.leads.store-call', $selectedLead?->id ?? 0)}}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="">{{__("attr.status")}} <span class="text-danger">*</span></label>
                        <div class="d-flex justify-content-start gap-2">
                            <div class="mr-3">
                                <input type="radio" class="btn-check" name="status"
                                       id="danger-outlined" value="declined" required>
                                <label class="btn btn-outline-danger"
                                       for="danger-outlined">
                                    <i class="fa-solid fa-phone-slash"></i>
                                    {{__("translation.leads.declined")}}
                                </label>
                            </div>
                            <div>
                                <input type="radio" class="btn-check" name="status"
                                       id="success-outlined" value="answered" required>
                                <label class="btn btn-outline-success"
                                       for="success-outlined">
                                    <i class="fa-solid fa-phone-volume"></i>
                                    {{ __("translation.leads.answered") }}
                                </label>
                            </div>
                            @error('status')
                            <div class="text-danger">* {{$message}}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comment">
                            {{__("translation.leads.comment")}}
                        </label>
                        <textarea name="comment" id="comment" rows="5" class="form-control"></textarea>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-success">
                            {{__("translation.btn.save")}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-preloader d-none" wire:loading.class.remove="d-none">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>