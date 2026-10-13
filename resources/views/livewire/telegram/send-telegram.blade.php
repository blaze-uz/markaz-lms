<div>
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <div class="card rounded-4">
                <div class="card-header rounded-top-4">
                    <h5 class="mb-0">@lang('translation.telegram.selected_receivers')</h5>
                </div>
                <div class="card-body">
                    <div class="overflow-auto" style="max-height: 500px">
                        @forelse ($receivers as $receiver)
                            <div class="mb-2 d-flex align-items-center">
                                <span class="me-2">
                                    {!! Avatar::create($receiver->getFullName())->setTheme('grayscale-dark')->setDimension(32)->setFontSize(12)->toSvg() !!}
                                </span>
                                <div>
                                    <div class="fw-medium">{{ $receiver->getFullName() }}</div>
                                    @if($receiver->telegram_chat_id)
                                        <small class="text-success">
                                            <i class="ri-telegram-line"></i> @lang('translation.telegram.connected')
                                        </small>
                                    @else
                                        <small class="text-danger">
                                            <i class="ri-error-warning-line"></i> @lang('translation.telegram.not_connected')
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">@lang('translation.telegram.no_receivers')</p>
                        @endforelse
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">{{ $receivers->count() }} @lang('translation.telegram.receivers_count')</small>
                        <br>
                        <small class="text-success">
                            <i class="ri-telegram-line"></i>
                            {{ $receivers->whereNotNull('telegram_chat_id')->count() }} @lang('translation.telegram.connected')
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-sm-12">
            <div class="card rounded-4">
                <div class="card-header rounded-top-4">
                    <h5 class="mb-0">@lang('translation.telegram.compose_message')</h5>
                </div>
                <div class="card-body">
                    @if (!$isBotConfigured)
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="ri-error-warning-line me-3 fs-3"></i>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading mb-2">@lang('translation.telegram.student_bot_not_configured')</h5>
                                <p class="mb-2">@lang('translation.telegram.student_bot_not_configured_message')</p>
                                <a href="{{ route('admin.settings.students-telegram-webapp') }}" class="btn btn-warning btn-sm">
                                    <i class="ri-settings-3-line me-1"></i>
                                    @lang('translation.telegram.configure_bot')
                                </a>
                            </div>
                        </div>
                    @else
                        <form wire:submit.prevent="sendMessage">
                            <div class="mb-3">
                                <label for="message" class="form-label">
                                    @lang('translation.telegram.message') <span class="text-danger">*</span>
                                </label>
                                <textarea 
                                    class="form-control @error('message') is-invalid @enderror" 
                                    id="message"
                                    wire:model="message"
                                    rows="10"
                                    placeholder="@lang('translation.telegram.message_placeholder')"></textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="mt-2 d-flex justify-content-between">
                                    <small class="text-muted">
                                        @lang('translation.telegram.characters'): 
                                        <span id="charCount">{{ strlen($message) }}</span>
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.students.index') }}" class="btn btn-light">
                                    @lang('translation.btn.cancel')
                                </a>
                                <button type="submit" class="btn btn-primary" 
                                    wire:loading.attr="disabled"
                                    wire:target="sendMessage">
                                    <span wire:loading.remove wire:target="sendMessage">
                                        <i class="ri-send-plane-line me-1"></i>
                                        @lang('translation.telegram.send')
                                    </span>
                                    <span wire:loading wire:target="sendMessage">
                                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                        @lang('translation.sending')...
                                    </span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('custom-scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('message-sent', () => {
                    // Optionally redirect or show success message
                });
            });

            // Character counter
            const messageTextarea = document.getElementById('message');
            if (messageTextarea) {
                messageTextarea.addEventListener('input', function() {
                    const charCount = document.getElementById('charCount');
                    if (charCount) {
                        charCount.textContent = this.value.length;
                    }
                });
            }
        </script>
    @endpush
</div>
