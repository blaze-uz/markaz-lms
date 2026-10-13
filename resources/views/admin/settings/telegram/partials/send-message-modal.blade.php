<!-- Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="sendMessageModalLabel">
                    @lang('translation.telegram.send_daily_report')
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.telegrams.send') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="date">
                            @lang('translation.telegram.which_date')
                        </label>
                        <input type="date" class="form-control" id="send_date" name="date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        @lang('translation.Close')
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        @lang('translation.telegram.send')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>