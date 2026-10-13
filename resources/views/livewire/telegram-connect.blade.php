<div>
    <div class="card">
        <div class="card-header bg-light">
            <h4 class="card-title mb-0">
                @lang('translation.connect')
            </h4>
        </div>
        <div class="card-body">
            <div class="form-check form-switch">
                <input wire:model="is_connected" wire:click="toggleConnection" class="form-check-input" type="checkbox"
                    id="telegram-status">
                <label class="form-check-label" for="telegram-status">
                    @lang('translation.connect')
                </label>
            </div>
            @if ($is_connected)
                <div class="mt-3">
                    <p class="mb-0">
                        @lang('translation.telegram.connected_telegrams')
                    </p>
                    <ul class="list-group list-group-flush">
                        @foreach ($telegrams as $telegram)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $telegram->chat_id }}</span>
                                <button type="button" class="btn btn-sm btn-danger"
                                    wire:click="$set('deleteId', {{ $telegram->id }})" data-bs-toggle="modal"
                                    data-bs-target="#deleteConfirmationModal">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="card-preloader d-none" wire:loading.class.remove="d-none">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">@lang('translation.confirm_delete')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @lang('translation.telegram.confirm_delete_message')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        @lang('translation.cancel')
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="deleteTelegram({{ $deleteId ?? 'null' }})"
                        data-bs-dismiss="modal">
                        @lang('translation.delete')
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="openTelegramConnectionModal" tabindex="-1"
        aria-labelledby="openTelegramConnectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="openTelegramConnectionModalLabel">
                        @lang('translation.redirect-to-telegram-app')
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <div class="text-muted  text-lg">
                            @lang('translation.time-remaining'): <span id="timer" class="fw-bold text-primary"></span>
                        </div>
                    </div>
                    @if ($qr && $redirect_url)
                        <div class="mb-4">
                            <img src="{{ $qr }}" alt="QR Code" class="img-fluid">
                        </div>
                        <div class="mb-3">
                            <a href="{{ $redirect_url }}" target="_blank" class="btn btn-primary">
                                <i class="ri-telegram-fill me-1"></i>
                                @lang('translation.open-in-telegram')
                            </a>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            @lang('translation.telegram.connection-error')
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        let timerInterval;
        const TOKEN_EXPIRY = {{ \App\Livewire\TelegramConnect::TOKEN_EXPIRY }};

        function startTimer(duration) {
            let timer = duration * 60;
            const timerDisplay = document.getElementById('timer');
            const modal = bootstrap.Modal.getInstance(document.getElementById('openTelegramConnectionModal'));

            function updateTimer() {
                const minutes = Math.floor(timer / 60);
                const seconds = timer % 60;

                timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

                if (--timer < 0) {
                    clearInterval(timerInterval);
                    modal.hide();
                    window.notifyMessage('@lang('translation.telegram.connection-timeout')', 'warning');
                }
            }

            updateTimer();
            timerInterval = setInterval(updateTimer, 1000);
        }

        $wire.on('openTelegramConnect', () => {
            const modal = new bootstrap.Modal(document.getElementById('openTelegramConnectionModal'));
            modal.show();
            startTimer(TOKEN_EXPIRY);
        });

        document.getElementById('openTelegramConnectionModal').addEventListener('hidden.bs.modal', () => {
            clearInterval(timerInterval);
        });

        $wire.on('showError', (message) => {
            window.notifyMessage(message[0].message, 'danger');
        });

        $wire.on('showSuccess', (message) => {
            window.notifyMessage(message[0].message, 'success');
        });
    </script>
@endscript
