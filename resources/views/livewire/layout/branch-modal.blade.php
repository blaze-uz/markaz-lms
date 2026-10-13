<div class="modal fade" id="branchModal" tabindex="-1" aria-labelledby="branchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="branchModalLabel">@lang('translation.branches.choose')</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.branches.change-current') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3 d-flex flex-column">
                        @foreach($branches as $branch)
                            <div class="w-100">

                                <input type="radio" class="btn-check" name="branch_id" id="branch_id-{{$branch->id}}"
                                       value="{{$branch->id}}"
                                       @if(auth()->user()->current_branch_id == $branch->id) checked @endif>
                                <label class="btn btn-outline-primary w-100" for="branch_id-{{$branch->id}}">
                                    @if(auth()->user()->current_branch_id == $branch->id)
                                        <i class=" ri-check-fill align-middle"></i>
                                    @endif
                                    {{$branch->name}}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            @lang("translation.Close")
                        </button>
                        <button type="submit" class="btn btn-primary">
                            @lang("translation.btn.save")
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>