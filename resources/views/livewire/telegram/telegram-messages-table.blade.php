<div>
    @push('css')
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    @endpush

    <div class="card rounded-4">
        <div class="border-0 card-header bg-soft-primary rounded-top-4">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0 fw-semibold me-3">
                    <i class="ri-telegram-line me-2"></i>
                    @lang('translation.telegram.messages')
                </h5>
                <div class="search-box flex-grow-1 flex-md-grow-0" style="min-width: 180px; max-width: 280px;">
                    <input type="search" class="form-control form-control-sm search-input" placeholder="@lang('translation.Search')"
                        wire:model.live.debounce.1000ms="search">
                    <i class="ri-search-line search-icon"></i>
                </div>
                <select class="form-select form-select-sm" wire:model.live="filterByStatus" style="width: auto; min-width: 100px;">
                    <option value="all">@lang('translation.all')</option>
                    <option value="sent">@lang('translation.sent')</option>
                    <option value="failed">@lang('translation.failed')</option>
                </select>
                <button type="button" class="btn btn-sm bg-white border text-nowrap" id="dateRange" data-livewire="@this" style="width: auto; min-width: 180px;">
                    <i class="far fa-calendar-alt me-1"></i>
                    <span>
                        {{ $date ?: __('translation.date_range') }}
                    </span>
                    <i class="fas fa-caret-down ms-1"></i>
                </button>
                <select class="form-select form-select-sm" wire:model.live="perPage" style="width: auto; min-width: 70px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <button type="button" class="btn btn-primary btn-sm flex-shrink-0 ms-auto" wire:click="openSendModal">
                    <i class="ri-send-plane-line me-1"></i>
                    @lang('translation.telegram.send_message')
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive table-card" wire:loading.class="opacity-50">
                @if ($messages->count())
                    <table class="table align-middle table-hover">
                        <thead class="align-middle table-light">
                            <tr>
                                <th>ID</th>
                                <th>@lang('translation.students.full_name')</th>
                                <th>@lang('translation.telegram.message')</th>
                                <th>@lang('attr.status')</th>
                                <th>@lang('translation.sms.send_at')</th>
                                <th>@lang('translation.created_by')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($messages as $message)
                                <tr wire:key="telegram-message-{{ $message->id }}">
                                    <td><span class="fw-medium">{{ $message->id }}</span></td>
                                    <td class="text-nowrap">
                                        @if($message->student)
                                            <a href="{{ route('admin.students.show', $message->student->id) }}"
                                                class="text-reset d-flex align-items-center">
                                                <span class="me-2">
                                                    {!! Avatar::create($message->student->getFullName())->setTheme('grayscale-dark')->setDimension(32)->setFontSize(12)->toSvg() !!}
                                                </span>
                                                <div>
                                                    <div class="fw-medium">{{ $message->student->getFullName() }}</div>
                                                    @if($message->student->phone)
                                                        <small class="text-muted">{{ $message->student->phone }}</small>
                                                    @endif
                                                </div>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-wrap" style="max-width: 300px;">
                                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ strip_tags($message->message) }}">
                                                {{ Str::limit(strip_tags($message->message), 60) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        @if($message->status === 'sent')
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="ri-check-line me-1"></i>@lang('translation.sent')
                                            </span>
                                        @else
                                            @php
                                                $isBotNotStarted = $message->error_log && (
                                                    str_contains($message->error_log, 'Forbidden') ||
                                                    str_contains($message->error_log, 'bot was blocked') ||
                                                    str_contains($message->error_log, 'chat not found')
                                                );
                                            @endphp
                                            <span class="badge {{ $isBotNotStarted ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger' }}"
                                                @if($message->error_log)
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="{{ $message->error_log }}"
                                                @endif>
                                                <i class="{{ $isBotNotStarted ? 'ri-user-unfollow-line' : 'ri-error-warning-line' }} me-1"></i>
                                                @if($isBotNotStarted)
                                                    @lang('translation.telegram.bot_not_started')
                                                @else
                                                    @lang('translation.failed')
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if($message->sent_at)
                                            <span class="text-muted">{{ $message->sent_at->format('d.m.Y H:i') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if($message->createdBy)
                                            <span class="text-muted">{{ $message->createdBy->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <x-no-result />
                @endif
            </div>

            <div class="mt-3">
                {{ $messages->links() }}
            </div>
        </div>
    </div>

    <!-- Send Message Modal -->
    @if($showSendModal)
        <div wire:ignore.self class="modal fade show" id="sendTelegramModal" tabindex="-1" aria-labelledby="sendTelegramModalLabel"
            aria-hidden="false" style="display: block; padding-right: 17px;">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-soft-primary">
                        <h5 class="modal-title fw-semibold" id="sendTelegramModalLabel">
                            <i class="ri-telegram-line me-2"></i>
                            @lang('translation.telegram.send_message')
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeSendModal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                <form wire:submit.prevent="sendMessage">
                    <div class="modal-body">
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
                            <div class="row g-3">
                                <!-- Student Selection -->
                                <div class="col-md-5">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 fw-semibold">
                                                @lang('translation.telegram.selected_receivers')
                                                @if(count($selectedStudents) > 0)
                                                    <span class="badge bg-primary ms-2">{{ count($selectedStudents) }}</span>
                                                @endif
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="search-box position-relative">
                                                    <input type="text" 
                                                        class="form-control" 
                                                        wire:model.live.debounce.300ms="studentSearch"
                                                        placeholder="@lang('translation.search_for_students')...">
                                                    <i class="ri-search-line search-icon"></i>
                                                </div>
                                            </div>
                                            <div class="overflow-auto" style="max-height: 400px;">
                                                @if(strlen($studentSearch) >= 2 && count($searchableStudents) > 0)
                                                    @foreach($searchableStudents as $student)
                                                        <div class="mb-2 p-2 border rounded"
                                                            style="cursor: pointer; transition: all 0.2s;"
                                                            onclick="Livewire.find('{{ $this->getId() }}').toggleStudent({{ $student->id }})">
                                                            <div class="form-check d-flex align-items-center">
                                                                <input class="form-check-input" 
                                                                    type="checkbox" 
                                                                    wire:model="selectedStudents"
                                                                    value="{{ $student->id }}"
                                                                    id="student-{{ $student->id }}">
                                                                <label class="form-check-label flex-grow-1 ms-2" for="student-{{ $student->id }}" style="cursor: pointer;">
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2">
                                                                            {!! Avatar::create($student->getFullName())->setTheme('grayscale-dark')->setDimension(32)->setFontSize(12)->toSvg() !!}
                                                                        </span>
                                                                        <div class="flex-grow-1">
                                                                            <div class="fw-medium">{{ $student->getFullName() }}</div>
                                                                            <div class="small text-muted">
                                                                                {{ $student->phone }}
                                                                                @if($student->telegram_chat_id)
                                                                                    <span class="badge bg-success-subtle text-success ms-1">
                                                                                        <i class="ri-telegram-line"></i>
                                                                                    </span>
                                                                                @else
                                                                                    <span class="badge bg-danger-subtle text-danger ms-1">
                                                                                        <i class="ri-error-warning-line"></i>
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @elseif(strlen($studentSearch) < 2)
                                                    <div class="text-center text-muted py-5">
                                                        <i class="ri-search-line fs-1 mb-2"></i>
                                                        <p>@lang('translation.search_by_name_surname_or_phone_number')</p>
                                                    </div>
                                                @else
                                                    <div class="text-center text-muted py-5">
                                                        <i class="ri-user-search-line fs-1 mb-2"></i>
                                                        <p>@lang('translation.no_data')</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Message Form -->
                                <div class="col-md-7">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 fw-semibold">@lang('translation.telegram.compose_message')</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="message" class="form-label">
                                                    @lang('translation.telegram.message') <span class="text-danger">*</span>
                                                </label>
                                                <textarea 
                                                    class="form-control @error('message') is-invalid @enderror" 
                                                    id="message"
                                                    wire:model="message"
                                                    rows="12"
                                                    placeholder="@lang('translation.telegram.message_placeholder')"></textarea>
                                                @error('message')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @error('selectedStudents')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="alert alert-info d-flex align-items-start">
                                                <i class="ri-information-line me-2 mt-1"></i>
                                                <div>
                                                    <strong>@lang('translation.note'):</strong>
                                                    <div class="small">@lang('translation.telegram.message_info')</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @if($isBotConfigured)
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeSendModal">
                                @lang('translation.btn.cancel')
                            </button>
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
                    @endif
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" wire:click="closeSendModal"></div>
    @endif

    @push('custom-scripts')
        <script type="text/javascript" src="{{ asset('assets/js/moment.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script>
            $(document).ready(function() {
                // Set locale moment
                moment.locale('{{ app()->getLocale() == 'ru' ? 'ru' : 'uz-latn' }}');
                
                // Parse the current date from Livewire component
                let currentDate = '{{ $date }}';
                let [startDate, endDate] = currentDate.split(' - ').map(date => moment(date, 'YYYY.MM.DD'));

                // Set the date range text initially
                $('#dateRange span').html(currentDate || '{{ __('translation.date_range') }}');

                // Date range as a button
                $('#dateRange').daterangepicker({
                    ranges: {
                        "{{ __('attr.today') }}": [moment(), moment()],
                        "{{ __('attr.yesterday') }}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        "{{ __('attr.last7days') }}": [moment().subtract(6, 'days'), moment()],
                        "{{ __('attr.last30days') }}": [moment().subtract(29, 'days'), moment()],
                        "{{ __('attr.thisMonth') }}": [moment().startOf('month'), moment().endOf('month')],
                        "{{ __('attr.lastMonth') }}": [
                            moment().subtract(1, 'months').startOf('month'),
                            moment().subtract(1, 'months').endOf('month')
                        ],
                        "{{ __('attr.thisYear') }}": [moment().startOf('year'), moment().endOf('year')],
                        "{{ __('attr.lastYear') }}": [
                            moment().subtract(1, 'years').startOf('year'),
                            moment().subtract(1, 'years').endOf('year')
                        ],
                    },
                    startDate: startDate.isValid() ? startDate : moment().subtract(29, 'days'),
                    endDate: endDate.isValid() ? endDate : moment(),
                    locale: {
                        "customRangeLabel": "{{ __('attr.optionalDate') }}",
                        "applyLabel": "{{ __('attr.save') }}",
                        "cancelLabel": "{{ __('attr.cancel') }}",
                    }
                }, function(start, end) {
                    let selected = start.format('YYYY.MM.DD') + ' - ' + end.format('YYYY.MM.DD');
                    eval($('#dateRange').attr('data-livewire')).set('date', selected);
                    $('#dateRange span').html(selected);
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

                // Modal handling
                Livewire.on('closeModal', () => {
                    $('#sendTelegramModal').modal('hide');
                });
            });

            // Show modal when showSendModal is true
            document.addEventListener('livewire:init', () => {
                Livewire.on('showSendModal', () => {
                    $('#sendTelegramModal').modal('show');
                });
            });
        </script>
    @endpush
</div>
