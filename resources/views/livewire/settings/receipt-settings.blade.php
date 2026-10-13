<div>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 card-title">@lang('translation.receipts.field_settings')</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">

                        @if($settings['show_header_text'])
                            <div class="mt-2">
                                <label class="form-label">@lang('translation.receipts.header_text')</label>
                                <textarea wire:model.live="header_text"
                                        class="form-control"
                                        rows="3"></textarea>
                            </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="form-label">@lang('translation.receipts.visible_fields')</label>
                        @foreach($settings as $key => $value)
                            <div class="mb-2 form-check form-switch">
                                <input wire:model="settings.{{ $key }}"
                                       wire:change="updateSetting('{{ $key }}', $event.target.checked)"
                                       class="form-check-input"
                                       type="checkbox"
                                       id="{{ $key }}">
                                <label class="form-check-label" for="{{ $key }}">
                                    @lang("translation.receipts.fields.$key")
                                </label>
                            </div>
                        @endforeach

                        @if($settings['show_logo'])
                            <div class="mt-3">
                                <label class="form-label">@lang('translation.receipts.logo_position')</label>
                                <div class="d-flex gap-2">
                                    <button wire:click="updateLogoPosition('top')"
                                            class="btn {{ $logo_position === 'top' ? 'btn-primary' : 'btn-outline-primary' }}">
                                        @lang('translation.receipts.logo_top')
                                    </button>
                                    <button wire:click="updateLogoPosition('bottom')"
                                            class="btn {{ $logo_position === 'bottom' ? 'btn-primary' : 'btn-outline-primary' }}">
                                        @lang('translation.receipts.logo_bottom')
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        @if($settings['show_footer'])
                            <div class="mt-2">
                                <label class="form-label">@lang('translation.receipts.footer_text')</label>
                                <textarea wire:model.live="footer_text"
                                        class="form-control"
                                        rows="3"></textarea>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-end">
                        <button wire:click="saveTexts" class="btn btn-primary">
                            @lang('translation.receipts.save')
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 card-title">@lang('translation.receipts.preview')</h5>
                </div>
                <div class="card-body">
                    <div class="mx-auto receipt_container">
                        <div class="card">
                            <div class="card-body">
                                @if($settings['show_logo'] && $logo_position === 'top')
                                    <div class="mb-3 d-flex justify-content-center">
                                        <img src="{{ $logo_path }}" alt="logo" width="150" class="mt-3 mb-2">
                                    </div>
                                @endif

                                <div class="receipt_header">
                                    <h1 class="mb-3">To'lov kvitansiyasi</h1>
                                    @if($settings['show_header_text'] && $header_text)
                                        <h2>{!! nl2br(e($header_text)) !!}</h2>
                                    @endif
                                </div>
                                <div class="receipt_body">
                                    <div class="date_time_con">
                                        <div class="date">{{ now()->format('d.m.Y') }}</div>
                                        <div class="time">{{ now()->format('H:i') }}</div>
                                    </div>
                                    <div class="items">
                                        @if($settings['show_payment_id'])
                                            <div class="mb-2 d-flex justify-content-between">
                                                <span>ID</span>
                                                <span>12345</span>
                                            </div>
                                        @endif

                                        @if($settings['show_student_info'])
                                            <div class="mb-2 d-flex justify-content-between">
                                                <span>@lang("translation.students.student")</span>
                                                <span>John Doe</span>
                                            </div>
                                        @endif

                                        @if($settings['show_group_info'])
                                            <div class="mb-2 d-flex justify-content-between">
                                                <span>@lang("translation.groups.group")</span>
                                                <span>Sample Group</span>
                                            </div>
                                            <div class="mb-2 d-flex justify-content-between">
                                                <span>@lang("translation.groups.debt_range")</span>
                                                <span>01.01.2025 - 31.01.2025</span>
                                            </div>
                                        @endif

                                        @if($settings['show_payment_type'])
                                            <div class="mb-2 d-flex justify-content-between">
                                                <span>@lang("translation.payments.payment_type")</span>
                                                <span>@lang("translation.payments.for_debt")</span>
                                            </div>
                                            <div class="mb-2 d-flex justify-content-between">
                                                <span>@lang("translation.payments.form_of_payment")</span>
                                                <span>@lang("translation.cash")</span>
                                            </div>
                                        @endif

                                        @if($settings['show_cashier'])
                                            <div class="mb-2 d-flex justify-content-between">
                                                <span>@lang("translation.payments.created_by")</span>
                                                <span>Admin User</span>
                                            </div>
                                        @endif

                                        @if($settings['show_amount'])
                                            <div class="d-flex justify-content-between">
                                                <h5 class="d-inline-block fw-semibold">@lang("translation.students.payment_amount")</h5>
                                                <h4 class="d-inline-block fw-semibold">100,000 UZS</h4>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if($settings['show_logo'] && $logo_position === 'bottom')
                                    <div class="mb-3 d-flex justify-content-center">
                                        <img src="{{ $logo_path }}" alt="logo" width="150" class="mt-3 mb-2">
                                    </div>
                                @endif
                                @if($settings['show_footer'] && $footer_text)
                                    <div class="mt-4 text-center">
                                        {!! nl2br(e($footer_text)) !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .receipt_container {
            width: 300px;
        }
        .receipt_container * {
            color: #000 !important;
        }
        .receipt_header {
            padding-bottom: 20px;
            border-bottom: 1px dashed #000;
            text-align: center;
        }
        .receipt_header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .receipt_header h2 {
            font-size: 14px;
            font-weight: 300;
        }
        .date_time_con {
            display: flex;
            justify-content: center;
            column-gap: 25px;
            margin: 15px 0;
        }
        .items {
            margin-top: 25px;
        }
        span {
            font-weight: 500;
        }
    </style>
</div>
