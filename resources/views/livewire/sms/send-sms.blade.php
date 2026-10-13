<div>
    <form action="{{ route('admin.sms.send-smart') }}" method="POST" id="sms-form">
        @csrf
        @foreach ($receivers as $receiver)
            <input type="hidden" name="sendSmsIds[]" value="{{ $receiver['id'] }}">
        @endforeach
        <input type="hidden" name="sendSmsType" value="{{ $sendSmsType }}">
        <input type="hidden" name="send_to_students" value="{{ $showStudents ? '1' : '0' }}">
        <input type="hidden" name="send_to_parents" value="{{ $showParents ? '1' : '0' }}">
        <input type="hidden" name="template_id" value="{{ $selectedTemplateId }}">

        <div class="row">
            @push('css')
                <style>
                    .bg-custom {
                        background-color: #0080ff42;
                    }

                    .provider-option {
                        transition: all 0.3s ease;
                    }

                    .hover-shadow:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
                    }

                    .cursor-pointer {
                        cursor: pointer;
                    }

                    .provider-option.border-primary {
                        border-width: 1.5px !important;
                    }

                    /* Fix modal padding issue */
                    body.modal-open {
                        padding-right: 0 !important;
                        overflow: hidden;
                    }

                    .modal {
                        padding-right: 0 !important;
                    }

                    /* Truncate long select options */
                    #sms-templates option {
                        max-width: 100%;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }

                    /* Modal table text truncation */
                    .modal .table td {
                        word-break: break-word;
                    }

                    .receiver-name {
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }

                    .sms-message-preview {
                        word-wrap: break-word;
                        overflow-wrap: break-word;
                        white-space: pre-wrap;
                    }

                    /* Modal width control */
                    #smsPreviewModal .modal-lg {
                        max-width: 900px !important;
                    }

                    /* Hide scrollbar but keep scroll functionality */
                    .hide-scrollbar {
                        -ms-overflow-style: none;
                        /* Internet Explorer 10+ */
                        scrollbar-width: none;
                        /* Firefox */
                    }

                    .hide-scrollbar::-webkit-scrollbar {
                        display: none;
                        /* Safari and Chrome */
                    }
                </style>
            @endpush

            <div class="col-md-4 col-sm-12">
                <div class="card rounded-4">
                    <div class="card-header rounded-top-4">@lang('translation.sms.selected_receivers')</div>
                    <div class="card-header">
                        <div class="gap-3 d-flex align-items-center">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="showStudents"
                                    wire:model.live="showStudents">
                                <label class="form-check-label cursor-pointer" for="showStudents">
                                    @lang('translation.students.students')
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="showParents"
                                    wire:model.live="showParents">
                                <label class="form-check-label cursor-pointer" for="showParents">
                                    @lang('translation.students.parents')
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-auto card-body" style="max-height: 400px">
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th></th>
                                    <th>@lang('translation.sms.receiver_name')</th>
                                    <th>@lang('translation.sms.phone')</th>
                                </tr>
                                @foreach ($receivers as $receiver)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @if ($showStudents || $showParents)
                                                <div>{{ $receiver['name'] }} {{ $receiver['surname'] }}</div>
                                            @endif
                                            @if ($showParents || ($showStudents && $showParents))
                                                <div class="text-muted small">Ota-onasi telefon raqami</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($showStudents && $showParents)
                                                <div>{{ formattedPhone($receiver['phone']) }}</div>
                                                @if ($receiver['parent_phone'])
                                                    <div class="text-muted small">
                                                        {{ formattedPhone($receiver['parent_phone']) }}</div>
                                                @endif
                                            @elseif ($showStudents)
                                                {{ formattedPhone($receiver['phone']) }}
                                            @elseif ($showParents)
                                                @if ($receiver['parent_phone'])
                                                    <div>-</div>
                                                    <div class="text-muted small">
                                                        {{ formattedPhone($receiver['parent_phone']) }}</div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5 col-sm-12">
                <div class="card rounded-4">
                    <div class="card-header rounded-top-4">@lang('translation.sms.template')</div>
                    <div class="card-body">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <div>
                                <label for="template">@lang('translation.sms.template')</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <select id="sms-templates" class="form-select" wire:model.live="selectedTemplateId">
                                <option value="">@lang('translation.sms.select_template')</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}" title="{{ $template->name }}">
                                        {{ Str::limit($template->name, 100) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="template-error" style="display: none;">
                                Iltimos, SMS shablonini tanlang!
                            </div>
                        </div>

                        @if ($selectedTemplate)
                            <div class="mb-3">
                                <label for="template">@lang('translation.sms.template')</label>
                                <textarea class="form-control" rows="5" disabled>{{ $selectedTemplate->content }}</textarea>
                            </div>
                        @endif

                        <div class="mb-3 form-check form-switch form-switch-md" dir="ltr">
                            <input type="checkbox" class="form-check-input" id="scheduleSmsStatus"
                                name="schedule_sms_status" wire:model.live="scheduleStatus">
                            <label class="form-check-label" for="scheduleSmsStatus" id="send-sms-label">
                                @lang('translation.sms.schedule_status')
                            </label>
                        </div>
                        @if ($scheduleStatus)
                            <div class="mb-3">
                                <label for="schedule_date">@lang('translation.sms.schedule_date')</label>
                                <input type="datetime-local" id="schedule_date" name="schedule_date"
                                    class="form-control">
                            </div>
                        @endif
                        @error('schedule_date')
                            <div class="text-danger">* {{ $message }}</div>
                        @enderror
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

            <div class="col-md-3 col-sm-12">
                <!-- SMS INFO -->
                <div class="card rounded-4">
                    <div class="card-header rounded-top-4 d-flex justify-content-between align-items-center">
                        <span>@lang('translation.sms.sms_info')</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Qabul qiluvchilar:</span>
                                <strong class="text-dark">{{ $receiverCount }} ta</strong>
                            </div>
                            <hr class="my-2">

                            @if ($selectedTemplate)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Matn turi:</span>
                                    <strong>
                                        @if ($isCyrillic)
                                            <span class="badge bg-info">Kirill</span>
                                        @else
                                            <span class="badge bg-secondary">Lotin</span>
                                        @endif
                                    </strong>
                                </div>
                                <hr class="my-2">

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Template belgilar:</span>
                                    <strong>{{ $characterCount }}</strong>
                                </div>
                                <hr class="my-2">

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">1 xabar uchun SMS:</span>
                                    <strong class="text-info">
                                        @if ($minSmsPerMessage == $maxSmsPerMessage)
                                            {{ $minSmsPerMessage }} qism
                                        @else
                                            {{ $minSmsPerMessage }}-{{ $maxSmsPerMessage }} qism
                                            @if ($avgSmsPerMessage > 0)
                                                <small class="text-muted">(o'rtacha: {{ $avgSmsPerMessage }})</small>
                                            @endif
                                        @endif
                                    </strong>
                                </div>
                                <hr class="my-2">
                            @endif

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Jami SMS:</span>
                                <strong class="text-primary fs-5">{{ $totalSmsCount }}</strong>
                            </div>
                            <hr class="my-2">

                            @if ($selectedTemplate && $selectedTemplate->eskiz_status)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">SMS turi:</span>
                                    <strong>
                                        @if ($selectedTemplate->eskiz_status == 'service')
                                            <span class="badge bg-primary">Xizmat</span>
                                        @else
                                            <span class="badge bg-warning">Reklama</span>
                                        @endif
                                    </strong>
                                </div>
                            @endif
                        </div>

                        @if ($receiverCount > 0)
                            <div class="alert alert-info small mb-0 py-2">
                                <i class="ri-information-line"></i>
                                @if ($showStudents && $showParents)
                                    O'quvchilar va ota-onalarga SMS yuboriladi
                                @elseif ($showStudents)
                                    Faqat o'quvchilarga SMS yuboriladi
                                @elseif ($showParents)
                                    Faqat ota-onalarga SMS yuboriladi
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning small mb-0 py-2">
                                <i class="ri-alert-line"></i>
                                SMS yuborish uchun kamida bitta checkbox tanlang
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="mb-3 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-primary" wire:click="openPreview" data-bs-toggle="modal"
                data-bs-target="#smsPreviewModal">
                <i class="ri-eye-line"></i> Ko'rib chiqish
            </button>
            <button class="btn btn-primary" type="submit" id="sendSmsBtn"
>
                <i class="ri-send-plane-fill"></i> @lang('translation.sms.send')
            </button>
        </div>
    </form>

    <!-- SMS Preview Modal - Full Blade/Livewire -->
    @if ($showPreviewModal)
        <div class="modal fade show" id="smsPreviewModal" tabindex="-1" aria-labelledby="smsPreviewModalLabel"
            style="display: block; padding-right: 0 !important; z-index: 1050;" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-lg" style="max-width: 900px;">
                <div class="modal-content">
                    <div class="modal-header bg-white">
                        <h5 class="modal-title">
                            <i class="ri-eye-line"></i> SMS Ko'rinishi
                        </h5>
                        <button type="button" class="btn-close" wire:click="closePreview"
                            data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-3">
                        @if (count($previewData) > 0)
                            @php
                                $totalPages = ceil(count($previewData) / $perPage);
                                $startIndex = ($currentPage - 1) * $perPage;
                                $paginatedPreviews = array_slice($previewData, $startIndex, $perPage);
                            @endphp
                            <!-- Statistics -->
                            <div class="border-bottom px-4 py-3">
                                <div class="row g-3">
                                    <div class="col-lg-3 col-sm-6">
                                        <div class="text-muted small">Qabul qiluvchilar</div>
                                        <div class="fw-bold fs-5">{{ $previewStats['total_count'] ?? 0 }} ta</div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6">
                                        <div class="text-muted small">Jami SMS</div>
                                        <div class="fw-bold fs-5">{{ $previewStats['total_sms'] ?? 0 }} ta</div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6">
                                        <div class="text-muted small">Sahifa</div>
                                        <div class="fw-bold fs-5">{{ $currentPage }} / {{ $totalPages }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagination Top -->
                            @if ($totalPages > 1)
                                <div class="d-flex justify-content-between align-items-center px-4 py-2 border-bottom">
                                    <div class="text-muted small">
                                        Ko'rsatilmoqda:
                                        <strong>{{ ($currentPage - 1) * $perPage + 1 }}-{{ min($currentPage * $perPage, count($previewData)) }}</strong>
                                        / <strong>{{ count($previewData) }}</strong>
                                    </div>
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0">
                                            <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                                                <button class="page-link"
                                                    wire:click="changePage({{ $currentPage - 1 }})"
                                                    {{ $currentPage == 1 ? 'disabled' : '' }}>
                                                    <i class="ri-arrow-left-s-line"></i>
                                                </button>
                                            </li>
                                            @for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                                <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                                    <button class="page-link"
                                                        wire:click="changePage({{ $i }})">{{ $i }}</button>
                                                </li>
                                            @endfor
                                            <li class="page-item {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                                                <button class="page-link"
                                                    wire:click="changePage({{ $currentPage + 1 }})"
                                                    {{ $currentPage == $totalPages ? 'disabled' : '' }}>
                                                    <i class="ri-arrow-right-s-line"></i>
                                                </button>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            @endif

                            <!-- Table -->
                            <div class="hide-scrollbar"
                                style="max-height: 500px; overflow-y: auto; overflow-x: auto;">
                                <table class="table table-hover table-sm align-middle mb-0"
                                    style="table-layout: auto; width: 100%;">
                                    <thead class="border-bottom position-sticky bg-white"
                                        style="top: 0; z-index: 10;">
                                        <tr>
                                            <th style="width: 50px; white-space: nowrap;" class="text-center">#</th>
                                            <th style="width: auto; white-space: nowrap;">Qabul qiluvchi</th>
                                            <th style="width: auto; white-space: nowrap">SMS matni</th>
                                            <th style="width: auto; white-space: nowrap;" class="text-center">SMS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($paginatedPreviews as $index => $preview)
                                            @php
                                                $actualIndex = ($currentPage - 1) * $perPage + $index + 1;
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <span class="text-muted">{{ $actualIndex }}</span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-medium" style="font-size: 13px;">
                                                            {{ $preview['receiver_name'] }}</div>
                                                        <small class="text-muted"
                                                            style="font-size: 11px;">{{ $preview['phone'] }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="p-2 bg-light rounded"
                                                        style="font-family: 'Courier New', monospace; font-size: 11px; max-height: 60px; overflow-y: auto; ">
                                                        {{ $preview['message'] }}</div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="text-muted">{{ $preview['sms_parts'] }} qism</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Bottom -->
                            @if ($totalPages > 1)
                                <div class="d-flex justify-content-between align-items-center px-4 py-2 border-top">
                                    <div class="text-muted small">
                                        Sahifa <strong>{{ $currentPage }}</strong> /
                                        <strong>{{ $totalPages }}</strong>
                                    </div>
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0">
                                            <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                                                <button class="page-link" wire:click="changePage(1)"
                                                    {{ $currentPage == 1 ? 'disabled' : '' }}>
                                                    <i class="ri-skip-back-mini-line"></i>
                                                </button>
                                            </li>
                                            <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                                                <button class="page-link"
                                                    wire:click="changePage({{ $currentPage - 1 }})"
                                                    {{ $currentPage == 1 ? 'disabled' : '' }}>
                                                    <i class="ri-arrow-left-s-line"></i>
                                                </button>
                                            </li>
                                            <li class="page-item {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                                                <button class="page-link"
                                                    wire:click="changePage({{ $currentPage + 1 }})"
                                                    {{ $currentPage == $totalPages ? 'disabled' : '' }}>
                                                    <i class="ri-arrow-right-s-line"></i>
                                                </button>
                                            </li>
                                            <li class="page-item {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                                                <button class="page-link"
                                                    wire:click="changePage({{ $totalPages }})"
                                                    {{ $currentPage == $totalPages ? 'disabled' : '' }}>
                                                    <i class="ri-skip-forward-mini-line"></i>
                                                </button>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning text-center m-3">
                                <i class="ri-alert-line"></i>
                                Template tanlanmagan yoki qabul qiluvchilar yo'q
                            </div>
                        @endif
                    </div>


                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>
    @endif

    <!-- SMS Confirmation Modal -->
    <div class="modal fade" id="smsConfirmModal" tabindex="-1" aria-labelledby="smsConfirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-opacity-10">
                    <h5 class="modal-title text-warning">
                        <i class="ri-alert-line"></i> SMS Yuborish Tasdiqlash
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="ri-information-line"></i>
                        <strong>Diqqat!</strong> Siz hoziroq SMS yubormoqchisiz. Bu amal bekor qilib bo'lmaydi.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center py-3 d-flex flex-column justify-content-center">
                                    <div class="text-muted small mb-2">
                                        <i class="ri-user-line"></i> Kimga yuboriladi
                                    </div>
                                    <div class="fw-bold fs-6">
                                        @if ($showStudents && $showParents)
                                            O'quvchilar va<br>Ota-onalar
                                        @elseif ($showStudents)
                                            Faqat o'quvchilar
                                        @elseif ($showParents)
                                            Faqat ota-onalar
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center py-3 d-flex flex-column justify-content-center">
                                    <div class="text-muted small mb-2">
                                        <i class="ri-message-2-line"></i> SMS soni
                                    </div>
                                    <div class="fw-bold fs-3 text-primary">{{ $receiverCount }} ta</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-center mb-0 fw-bold text-dark fs-6">
                        SMS yuborishni tasdiqlaysizmi?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i> Yo'q, bekor qilish
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmSendBtn">
                        <i class="ri-send-plane-fill"></i> Ha, yuborish
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const smsForm = document.getElementById('sms-form');
            const templateSelect = document.getElementById('sms-templates');
            const templateError = document.getElementById('template-error');
            const confirmModal = new bootstrap.Modal(document.getElementById('smsConfirmModal'));
            const confirmSendBtn = document.getElementById('confirmSendBtn');

            // Template o'zgarganda xatolikni olib tashlash
            if (templateSelect) {
                templateSelect.addEventListener('change', function() {
                    if (this.value) {
                        this.classList.remove('is-invalid');
                        if (templateError) {
                            templateError.style.display = 'none';
                        }
                    }
                });
            }

            // Confirm modal ichidagi "Ha, yuborish" tugmasi
            if (confirmSendBtn) {
                confirmSendBtn.addEventListener('click', function() {
                    confirmModal.hide();
                    // Event listener ni olib tashlash va formni yuborish
                    smsForm.removeEventListener('submit', handleFormSubmit);
                    smsForm.submit();
                });
            }

            function handleFormSubmit(e) {
                e.preventDefault();
                let hasError = false;

                // Template tanlangan yoki yo'qligini tekshirish
                if (!templateSelect || !templateSelect.value) {
                    templateSelect.classList.add('is-invalid');
                    if (templateError) {
                        templateError.style.display = 'block';
                    }
                    hasError = true;
                }

                // Qabul qiluvchilar borligini tekshirish
                const receiverCount = {{ $receiverCount }};
                if (receiverCount === 0) {
                    alert('Qabul qiluvchilar yo\'q! Iltimos, o\'quvchilar yoki ota-onalar checkboxini tanlang.');
                    hasError = true;
                }

                // Xatolik bo'lsa, submit qilmaslik
                if (hasError) {
                    return false;
                }

                // Har doim confirmation modal ko'rsatish
                confirmModal.show();
            }

            if (smsForm) {
                smsForm.addEventListener('submit', handleFormSubmit);
            }
        });
    </script>
@endpush
