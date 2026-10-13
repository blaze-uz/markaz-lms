@extends("layouts.master")
@section('page_container_class', 'w-100 px-0')
@section("title")
@lang('translation.leads.settings')
@endsection
@section("css")
<style>
    .parent>.row {
        display: flex;
        align-items: center;
        height: 100%;
    }

    .img-container img {
        height: 100px;
        width: 100%;
        cursor: pointer;
        transition: transform 1s;
        object-fit: cover;
    }

    .img-container label {
        overflow: hidden;
        position: relative;
    }

    .imgbgchk:checked+label {
        border: 1px dashed #3f5189;
        /* add rounded-4 */
        border-radius: 10px;
    }


    .imgbgchk:checked+label>.tick_container {
        opacity: 1;
    }

    .imgbgchk:checked+label>img {
        transform: scale(1.25);
        opacity: 0.3;
    }

    .tick_container {
        transition: .5s ease;
        opacity: 0;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        -ms-transform: translate(-50%, -50%);
        cursor: pointer;
        text-align: center;
    }

    .tick {
        background-color: #3f5189;
        color: white;
        font-size: 16px;
        padding: 9px 12px;
        height: 40px;
        width: 40px;
        border-radius: 100%;
    }
    
    .api-key-container {
        position: relative;
    }
    
    .api-key-container .copy-icon {
        position: absolute;
        right: 10px;
        top: 10px;
        cursor: pointer;
    }
    
    .api-key-container .copy-success {
        display: none;
        color: #0ab39c;
        position: absolute;
        right: 40px;
        top: 10px;
    }

    .dynamic-fields-section .dynamic-field-row {
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04);
    }

    .dynamic-fields-section .field-key-input:not(:focus) {
        font-family: ui-monospace, monospace;
        font-size: 0.9rem;
    }

    .dynamic-fields-section .select-options-editor {
        border-style: dashed !important;
    }

    .dynamic-fields-section .select-option-row .form-control {
        min-width: 0;
    }

    .leads-settings-nav-card .nav-pills .nav-link {
        color: var(--vz-body-color, #212529);
        padding: 0.65rem 0.9rem;
        border-radius: 0.375rem;
        background-color: transparent;
    }

    .leads-settings-nav-card .nav-pills .nav-link:hover,
    .leads-settings-nav-card .nav-pills .nav-link:focus-visible {
        background-color: transparent;
        color: var(--vz-body-color, #212529);
    }

    .leads-settings-nav-card .nav-pills .nav-link.active,
    .leads-settings-nav-card .nav-pills .nav-link.active:hover,
    .leads-settings-nav-card .nav-pills .nav-link.active:focus-visible {
        font-weight: 600;
        background-color: var(--vz-primary, #405189);
        color: #fff;
    }

    .leads-settings-nav-card .nav-pills .nav-link.active i,
    .leads-settings-nav-card .nav-pills .nav-link.active:hover i {
        color: #fff;
    }

    .leads-settings-pane-card {
        min-height: 14rem;
    }

    @media (min-width: 992px) {
        .leads-settings-nav-card.sticky-lg-top {
            top: 5.5rem;
            z-index: 101;
        }
    }
</style>

@endsection
@section("content")
<div class="row g-0">
    <div class="col-12 px-3 px-lg-4">
        {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
        <form action="{{ route('admin.settings.leads.update') }}" method="post" id="leads-settings-form">
            @csrf
            @method('PUT')
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                <h4 class="mb-0">@lang('translation.leads.settings')</h4>
                <button class="btn btn-primary d-sm-none" type="submit">@lang('translation.btn.save')</button>
            </div>
            <div class="row g-3 align-items-start">
                <div class="col-12 col-lg-4 col-xl-3">
                    <div class="card shadow-sm leads-settings-nav-card sticky-lg-top">
                        <div class="card-header py-3">
                            <span class="fw-semibold text-body">@lang('translation.leads.settings_nav_title')</span>
                        </div>
                        <div class="card-body p-2">
                            <ul class="nav nav-pills flex-column gap-1" id="leadsSettingsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active w-100 text-start" id="leads-tab-layout-btn" data-bs-toggle="tab" data-bs-target="#leads-tab-layout" type="button" role="tab" aria-controls="leads-tab-layout" aria-selected="true">
                                        <i class="ri-layout-grid-line me-2"></i>@lang('translation.leads.settings_tab_layout')
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link w-100 text-start" id="leads-tab-fields-btn" data-bs-toggle="tab" data-bs-target="#leads-tab-fields" type="button" role="tab" aria-controls="leads-tab-fields" aria-selected="false">
                                        <i class="ri-list-settings-line me-2"></i>@lang('translation.leads.settings_tab_fields')
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link w-100 text-start" id="leads-tab-api-btn" data-bs-toggle="tab" data-bs-target="#leads-tab-api" type="button" role="tab" aria-controls="leads-tab-api" aria-selected="false">
                                        <i class="ri-code-s-slash-line me-2"></i>@lang('translation.leads.settings_tab_api')
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-8 col-xl-9">
                    <div class="tab-content" id="leadsSettingsTabContent">
                    <!-- Tab: Lead view -->
                    <div class="tab-pane fade show active" id="leads-tab-layout" role="tabpanel" aria-labelledby="leads-tab-layout-btn" tabindex="0">
                        <div class="card shadow-sm leads-settings-pane-card">
                            <div class="card-header border-bottom py-3">
                                <h5 class="card-title mb-0">@lang("translation.leads.page")</h5>
                                <p class="text-muted small mb-0 mt-1">@lang("translation.leads.page_desc")</p>
                            </div>
                            <div class="card-body">
                        <div class="parent">
                            <div class="gap-4 gap-md-5 d-flex flex-wrap justify-content-center justify-content-md-start">
                            <div class='text-center img-container rounded-4'>
                                <div>
                                    <input type="radio" name="leads_page" id="img1" class="d-none imgbgchk"
                                        value="board" @if($lead->value == 'board') checked @endif>
                                    <label for="img1">
                                        <img src="{{ asset('assets/images/kanban/leads-board.webp') }}" alt="Image 1"
                                            class="img-thumbnail">
                                        <div class="tick_container">
                                            <div class="tick"><i class="fa fa-check"></i></div>
                                        </div>
                                    </label>
                                </div>
                                <div>
                                    <h5 class="fs-6 mt-2 mb-0">
                                        @lang("translation.leads.board")
                                    </h5>
                                </div>
                            </div>
                            <div class='text-center img-container'>
                                <div>
                                    <input type="radio" name="leads_page" id="img2" class="d-none imgbgchk"
                                        value="table" @if($lead->value == 'table') checked @endif>
                                    <label for="img2">
                                        <img src="{{ asset('assets/images/kanban/leads-table.webp') }}" alt="Image 2"
                                            class="img-thumbnail">
                                        <div class="tick_container">
                                            <div class="tick"><i class="fa fa-check"></i></div>
                                        </div>
                                    </label>
                                </div>
                                <div>
                                    <h5 class="fs-6 mt-2 mb-0">
                                        @lang("translation.leads.table")
                                    </h5>
                                </div>
                            </div>
                            </div>
                        </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Custom fields -->
                    <div class="tab-pane fade" id="leads-tab-fields" role="tabpanel" aria-labelledby="leads-tab-fields-btn" tabindex="0">
                        <div class="card shadow-sm leads-settings-pane-card">
                            <div class="card-header border-bottom py-3">
                                <h5 class="card-title mb-0">@lang('translation.leads.dynamic_fields_title')</h5>
                                <p class="text-muted small mb-0 mt-1">@lang('translation.leads.dynamic_fields_desc')</p>
                            </div>
                            <div class="card-body">
                    <div class="dynamic-fields-section">
                        <div id="dynamic-fields-rows" class="d-flex flex-column gap-3"
                            data-hint-auto="{{ e(__('translation.leads.field_key_auto_hint')) }}"
                            data-hint-manual="{{ e(__('translation.leads.field_key_manual')) }}"
                            data-opt-placeholder="{{ e(__('translation.leads.select_option_placeholder')) }}"
                            data-remove-title="{{ e(__('translation.leads.remove_select_option')) }}">
                            @forelse($leadDynamicFields as $i => $field)
                                @php
                                    $optLines = [];
                                    if (! empty($field['options_raw'] ?? '')) {
                                        foreach (preg_split("/\r\n|\r|\n/", $field['options_raw']) as $ln) {
                                            $t = trim((string) $ln);
                                            if ($t !== '') {
                                                $optLines[] = $t;
                                            }
                                        }
                                    }
                                @endphp
                                <div class="dynamic-field-row card card-body border" data-sync-key="0">
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-5">
                                            <label class="form-label fw-medium">@lang('translation.leads.field_label')</label>
                                            <input type="text" name="dynamic_fields[{{ $i }}][label]" class="form-control field-label-input" value="{{ $field['label'] }}" required autocomplete="off">
                                        </div>
                                        <div class="col-12 col-lg-4">
                                            <label class="form-label fw-medium">@lang('translation.leads.field_key')</label>
                                            <input type="text" name="dynamic_fields[{{ $i }}][key]" class="form-control field-key-input" value="{{ $field['key'] }}" required pattern="[a-z0-9_]+" maxlength="100" autocomplete="off">
                                            <small class="text-muted field-key-hint d-block mt-1">@lang('translation.leads.field_key_manual')</small>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-2">
                                            <label class="form-label fw-medium">@lang('translation.leads.field_type')</label>
                                            <select name="dynamic_fields[{{ $i }}][type]" class="form-select dynamic-field-type">
                                                @foreach(['text' => 'text', 'textarea' => 'textarea', 'number' => 'number', 'select' => 'select'] as $val => $lbl)
                                                    <option value="{{ $val }}" @selected($field['type'] === $val)>{{ $lbl }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-3 d-flex align-items-end gap-2 flex-wrap">
                                            <div class="form-check mb-0">
                                                <input type="checkbox" name="dynamic_fields[{{ $i }}][required]" value="1" class="form-check-input" id="req{{ $i }}" @checked($field['required'] ?? false)>
                                                <label class="form-check-label" for="req{{ $i }}">@lang('translation.leads.field_required')</label>
                                            </div>
                                            <button type="button" class="btn btn-soft-danger btn-sm remove-dynamic-field ms-auto">@lang('translation.leads.remove_field')</button>
                                        </div>
                                        <div class="col-12 options-wrap pt-1 @if(($field['type'] ?? '') !== 'select') d-none @endif">
                                            <label class="form-label fw-medium">@lang('translation.leads.field_options')</label>
                                            <p class="small text-muted mb-2">@lang('translation.leads.field_options_help')</p>
                                            <div class="select-options-editor border bg-light bg-opacity-50 p-3 rounded-3">
                                                <div class="select-options-rows d-flex flex-column gap-2">
                                                    @forelse($optLines as $opt)
                                                        <div class="input-group input-group-sm select-option-row">
                                                            <span class="input-group-text text-muted"><i class="ri-list-check"></i></span>
                                                            <input type="text" class="form-control select-option-input" value="{{ $opt }}" placeholder="@lang('translation.leads.select_option_placeholder')" autocomplete="off">
                                                            <button type="button" class="btn btn-outline-secondary remove-select-option" title="@lang('translation.leads.remove_select_option')"><i class="ri-subtract-line"></i></button>
                                                        </div>
                                                    @empty
                                                        @if(($field['type'] ?? '') === 'select')
                                                            <div class="input-group input-group-sm select-option-row">
                                                                <span class="input-group-text text-muted"><i class="ri-list-check"></i></span>
                                                                <input type="text" class="form-control select-option-input" value="" placeholder="@lang('translation.leads.select_option_placeholder')" autocomplete="off">
                                                                <button type="button" class="btn btn-outline-secondary remove-select-option" title="@lang('translation.leads.remove_select_option')"><i class="ri-subtract-line"></i></button>
                                                            </div>
                                                        @endif
                                                    @endforelse
                                                </div>
                                                <button type="button" class="btn btn-sm btn-soft-primary mt-3 add-select-option">
                                                    <i class="ri-add-line"></i> @lang('translation.leads.add_select_option')
                                                </button>
                                            </div>
                                            <textarea name="dynamic_fields[{{ $i }}][options_raw]" class="options-raw-field visually-hidden" tabindex="-1" aria-hidden="true">{{ $field['options_raw'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @empty
                            @endforelse
                        </div>
                        @error('dynamic_fields')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        <button type="button" class="btn btn-soft-primary mt-2" id="add-dynamic-field">
                            <i class="ri-add-line"></i> @lang('translation.leads.add_dynamic_field')
                        </button>
                    </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: API -->
                    <div class="tab-pane fade" id="leads-tab-api" role="tabpanel" aria-labelledby="leads-tab-api-btn" tabindex="0">
                        <div class="card shadow-sm leads-settings-pane-card">
                            <div class="card-header border-bottom py-3">
                                <h5 class="card-title mb-0">@lang('translation.leads.settings_tab_api')</h5>
                                <p class="text-muted small mb-0 mt-1">@lang("translation.leads.api_key_desc")</p>
                            </div>
                            <div class="card-body">
                        <div class="row g-4">
                            <div class="col-12 col-xl-7">
                                <h6 class="mb-2">@lang("translation.leads.api_key")</h6>
                                <div class="api-key-container mb-2">
                                    <input type="text" class="form-control form-control-lg font-monospace" id="apiKey" value="{{ $apiKey->value }}" readonly>
                                    <i class="ri-file-copy-line copy-icon" id="copyApiKey" role="button" tabindex="0" title="Copy"></i>
                                    <span class="copy-success" id="copySuccess">@lang("translation.copied")</span>
                                </div>
                                <small class="text-muted d-block mb-4">@lang("translation.leads.api_key_warning")</small>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="regenerateApiKey" name="regenerate_api_key" value="1">
                                    <label class="form-check-label" for="regenerateApiKey">
                                        @lang("translation.leads.regenerate_api_key")
                                    </label>
                                </div>
                                <div class="alert alert-warning mt-3 mb-0" id="regenerateWarning" style="display: none;">
                                    <i class="ri-alert-line me-2"></i>
                                    @lang("translation.leads.regenerate_api_key_warning")
                                </div>
                            </div>
                        </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3 d-none d-sm-flex">
                <button class="btn btn-primary px-4" type="submit">
                    <i class="ri-save-line me-1"></i>@lang('translation.btn.save')
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let dynamicFieldIndex = {{ count($leadDynamicFields) }};
        const rowsContainer = document.getElementById('dynamic-fields-rows');
        const addBtn = document.getElementById('add-dynamic-field');
        const I = rowsContainer ? {
            hintAuto: rowsContainer.dataset.hintAuto || '',
            hintManual: rowsContainer.dataset.hintManual || '',
            optPh: rowsContainer.dataset.optPlaceholder || '',
            removeTitle: rowsContainer.dataset.removeTitle || '',
        } : {};

        function slugifyKey(label) {
            if (!label || typeof label !== 'string') return '';
            let s = label.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            s = s.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '').replace(/_+/g, '_');
            return s.substring(0, 100);
        }

        function updateKeyHint(row) {
            const hint = row.querySelector('.field-key-hint');
            if (!hint) return;
            const sync = row.getAttribute('data-sync-key') === '1';
            hint.textContent = sync ? I.hintAuto : I.hintManual;
        }

        function bindLabelKeyAuto(row) {
            const labelIn = row.querySelector('.field-label-input');
            const keyIn = row.querySelector('.field-key-input');
            if (!labelIn || !keyIn) return;

            labelIn.addEventListener('input', function() {
                if (row.getAttribute('data-sync-key') !== '1') return;
                const next = slugifyKey(labelIn.value);
                keyIn.value = next;
            });

            keyIn.addEventListener('input', function() {
                const auto = slugifyKey(labelIn.value);
                if (keyIn.value === '' || keyIn.value === auto) {
                    row.setAttribute('data-sync-key', '1');
                } else {
                    row.setAttribute('data-sync-key', '0');
                }
                updateKeyHint(row);
            });

            row.addEventListener('focusin', function(ev) {
                if (ev.target !== keyIn) return;
                if (row.getAttribute('data-sync-key') === '1' && keyIn.value === '' && labelIn.value) {
                    keyIn.value = slugifyKey(labelIn.value);
                }
            });
        }

        function syncOptionsTextarea(wrap) {
            const ta = wrap.querySelector('.options-raw-field');
            const inputs = wrap.querySelectorAll('.select-option-input');
            if (!ta) return;
            const lines = Array.from(inputs).map(function(el) { return el.value.trim(); }).filter(Boolean);
            ta.value = lines.join('\n');
        }

        function wireSelectOptionRow(rowEl, wrap, list) {
            const inp = rowEl.querySelector('.select-option-input');
            const rm = rowEl.querySelector('.remove-select-option');
            if (inp && !inp.dataset.wired) {
                inp.dataset.wired = '1';
                inp.addEventListener('input', function() { syncOptionsTextarea(wrap); });
            }
            if (rm && !rm.dataset.wired) {
                rm.dataset.wired = '1';
                rm.addEventListener('click', function() {
                    if (list.querySelectorAll('.select-option-row').length <= 1) {
                        if (inp) inp.value = '';
                        syncOptionsTextarea(wrap);
                        return;
                    }
                    rowEl.remove();
                    syncOptionsTextarea(wrap);
                });
            }
        }

        function appendSelectOptionRow(list, wrap, value) {
            const rowEl = document.createElement('div');
            rowEl.className = 'input-group input-group-sm select-option-row';
            const span = document.createElement('span');
            span.className = 'input-group-text text-muted';
            span.innerHTML = '<i class="ri-list-check"></i>';
            const inp = document.createElement('input');
            inp.type = 'text';
            inp.className = 'form-control select-option-input';
            inp.value = value;
            inp.placeholder = I.optPh;
            inp.autocomplete = 'off';
            const rm = document.createElement('button');
            rm.type = 'button';
            rm.className = 'btn btn-outline-secondary remove-select-option';
            rm.title = I.removeTitle;
            rm.innerHTML = '<i class="ri-subtract-line"></i>';
            rowEl.appendChild(span);
            rowEl.appendChild(inp);
            rowEl.appendChild(rm);
            list.appendChild(rowEl);
            wireSelectOptionRow(rowEl, wrap, list);
        }

        function bindAddSelectOptionButton(editor, list, wrap) {
            const addBtnEl = editor.querySelector('.add-select-option');
            if (addBtnEl && !addBtnEl.dataset.bound) {
                addBtnEl.dataset.bound = '1';
                addBtnEl.addEventListener('click', function() {
                    appendSelectOptionRow(list, wrap, '');
                    syncOptionsTextarea(wrap);
                });
            }
        }

        function hydrateSelectFromTextarea(wrap) {
            const editor = wrap.querySelector('.select-options-editor');
            const list = wrap.querySelector('.select-options-rows');
            const ta = wrap.querySelector('.options-raw-field');
            if (!editor || !list || !ta) return;
            list.innerHTML = '';
            const lines = ta.value.split(/\r?\n/).map(function(s) { return s.trim(); }).filter(Boolean);
            const vals = lines.length ? lines : [''];
            vals.forEach(function(line) {
                appendSelectOptionRow(list, wrap, line);
            });
            bindAddSelectOptionButton(editor, list, wrap);
            syncOptionsTextarea(wrap);
        }

        function toggleOptions(selectEl) {
            const row = selectEl.closest('.dynamic-field-row');
            if (!row) return;
            const wrap = row.querySelector('.options-wrap');
            if (!wrap) return;
            const isSel = selectEl.value === 'select';
            wrap.classList.toggle('d-none', !isSel);
            if (isSel) hydrateSelectFromTextarea(wrap);
        }

        function bindRow(row) {
            updateKeyHint(row);
            bindLabelKeyAuto(row);
            const typeSelect = row.querySelector('.dynamic-field-type');
            if (typeSelect) {
                typeSelect.addEventListener('change', function() { toggleOptions(this); });
                toggleOptions(typeSelect);
            }
            const removeBtn = row.querySelector('.remove-dynamic-field');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() { row.remove(); });
            }
        }

        if (rowsContainer) {
            rowsContainer.querySelectorAll('.dynamic-field-row').forEach(bindRow);

            const form = rowsContainer.closest('form');
            if (form) {
                form.addEventListener('submit', function() {
                    rowsContainer.querySelectorAll('.options-wrap').forEach(syncOptionsTextarea);
                });
            }
        }

        if (rowsContainer && addBtn) {
            addBtn.addEventListener('click', function() {
                const i = dynamicFieldIndex++;
                const html = `
                <div class="dynamic-field-row card card-body border" data-sync-key="1">
                    <div class="row g-3">
                        <div class="col-12 col-lg-5">
                            <label class="form-label fw-medium">@lang('translation.leads.field_label')</label>
                            <input type="text" name="dynamic_fields[${i}][label]" class="form-control field-label-input" required autocomplete="off">
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-medium">@lang('translation.leads.field_key')</label>
                            <input type="text" name="dynamic_fields[${i}][key]" class="form-control field-key-input" required pattern="[a-z0-9_]+" maxlength="100" autocomplete="off">
                            <small class="text-muted field-key-hint d-block mt-1"></small>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label fw-medium">@lang('translation.leads.field_type')</label>
                            <select name="dynamic_fields[${i}][type]" class="form-select dynamic-field-type">
                                <option value="text">text</option>
                                <option value="textarea">textarea</option>
                                <option value="number">number</option>
                                <option value="select">select</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3 d-flex align-items-end gap-2 flex-wrap">
                            <div class="form-check mb-0">
                                <input type="checkbox" name="dynamic_fields[${i}][required]" value="1" class="form-check-input" id="req${i}">
                                <label class="form-check-label" for="req${i}">@lang('translation.leads.field_required')</label>
                            </div>
                            <button type="button" class="btn btn-soft-danger btn-sm remove-dynamic-field ms-auto">@lang('translation.leads.remove_field')</button>
                        </div>
                        <div class="col-12 options-wrap pt-1 d-none">
                            <label class="form-label fw-medium">@lang('translation.leads.field_options')</label>
                            <p class="small text-muted mb-2">@lang('translation.leads.field_options_help')</p>
                            <div class="select-options-editor border bg-light bg-opacity-50 p-3 rounded-3">
                                <div class="select-options-rows d-flex flex-column gap-2"></div>
                                <button type="button" class="btn btn-sm btn-soft-primary mt-3 add-select-option">
                                    <i class="ri-add-line"></i> @lang('translation.leads.add_select_option')
                                </button>
                            </div>
                            <textarea name="dynamic_fields[${i}][options_raw]" class="options-raw-field visually-hidden" tabindex="-1" aria-hidden="true"></textarea>
                        </div>
                    </div>
                </div>`;
                const d = document.createElement('div');
                d.innerHTML = html.trim();
                const row = d.firstElementChild;
                rowsContainer.appendChild(row);
                bindRow(row);
            });
        }

        const copyWrapper = function() {
            const copyApiKey = document.getElementById('copyApiKey');
            const apiKey = document.getElementById('apiKey');
            const copySuccess = document.getElementById('copySuccess');
            if (!copyApiKey || !apiKey) return;
            const doCopy = function() {
                apiKey.select();
                apiKey.setSelectionRange(0, 99999);
                document.execCommand('copy');
                if (copySuccess) {
                    copySuccess.style.display = 'inline';
                    setTimeout(function() {
                        copySuccess.style.display = 'none';
                    }, 2000);
                }
            };
            copyApiKey.addEventListener('click', doCopy);
            copyApiKey.addEventListener('keydown', function(ev) {
                if (ev.key === 'Enter' || ev.key === ' ') {
                    ev.preventDefault();
                    doCopy();
                }
            });
        };
        copyWrapper();

        const regenerateApiKey = document.getElementById('regenerateApiKey');
        const regenerateWarning = document.getElementById('regenerateWarning');
        if (regenerateApiKey && regenerateWarning) {
            regenerateApiKey.addEventListener('change', function() {
                regenerateWarning.style.display = this.checked ? 'block' : 'none';
            });
        }

        const btnIdByHash = {
            layout: 'leads-tab-layout-btn',
            fields: 'leads-tab-fields-btn',
            api: 'leads-tab-api-btn',
        };
        const hashByBtnId = {
            'leads-tab-layout-btn': 'layout',
            'leads-tab-fields-btn': 'fields',
            'leads-tab-api-btn': 'api',
        };
        function showTabFromHash() {
            const h = (window.location.hash || '').replace(/^#/, '');
            const btnId = btnIdByHash[h];
            if (!btnId || typeof bootstrap === 'undefined') return;
            const el = document.getElementById(btnId);
            if (!el) return;
            try {
                bootstrap.Tab.getOrCreateInstance(el).show();
            } catch (e) {}
        }
        showTabFromHash();
        window.addEventListener('hashchange', showTabFromHash);
        document.querySelectorAll('#leadsSettingsTabs [data-bs-toggle="tab"]').forEach(function(btn) {
            btn.addEventListener('shown.bs.tab', function() {
                const key = hashByBtnId[btn.id];
                if (key) {
                    history.replaceState(null, '', '#' + key);
                }
            });
        });
    });
</script>
@endsection