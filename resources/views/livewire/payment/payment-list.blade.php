<div>
    @push('css')
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/daterangepicker.min.css') }}" />
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-selection__clear {
                position: absolute !important;
                right: 20px !important;
                /* make it middle */
                top: 50% !important;
                transform: translateY(-50%) !important;
            }

            .filter-section {
                max-height: 0;
                opacity: 0;
                overflow: hidden;
                transition: all 0.3s ease-in-out;
                width: 100%;
            }

            .filter-section.show {
                max-height: none;
                opacity: 1;
            }

            .filter-section .filter-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: .5rem;
                width: 100%;
            }

            .filter-section .filter-item {
                width: 100%;
            }

            @media (max-width: 768px) {
                .filter-section.show {
                    max-height: none;
                }

                .filter-section .filter-grid {
                    grid-template-columns: 1fr;
                }

                .filter-section .filter-item {
                    margin-bottom: 0.5rem;
                }

                .filter-section .filter-item:last-child {
                    margin-bottom: 0;
                }
            }

            .action-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                align-items: center;
            }

            .action-buttons .btn {
                white-space: nowrap;
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
            }

            @media (max-width: 768px) {
                .action-buttons {
                    width: 100%;
                    justify-content: flex-start;
                }

                .action-buttons .search-wrapper {
                    width: 100%;
                    margin-bottom: 0.5rem;
                }

                .action-buttons .btn-group {
                    display: flex;
                    gap: 0.5rem;
                    width: 100%;
                }

                .action-buttons .btn-group .btn {
                    flex: 1;
                }
            }
        </style>
    @endpush

    <div class="flex-wrap d-flex justify-content-between align-items-baseline">
        <div class="flex-wrap gap-3 d-flex justify-content-between align-items-center w-100">
            <div class="action-buttons">
                <div class="search-wrapper">
                    <x-search parent-class="m-0" />
                </div>
                <div class="btn-group">
                    @can('payment-card-data')
                        @if ($showCards)
                            <button class="btn bg-primary-subtle text-primary" id="hide-card-button"
                                wire:click="toggleCards">
                                <i class="fa-solid fa-square-minus"></i> {{ __('translation.hide') }}
                            </button>
                        @else
                            <button class="btn bg-primary-subtle text-primary" id="show-card-button"
                                wire:click="toggleCards">
                                <i class="fa-solid fa-square-plus"></i> {{ __('translation.show') }}
                            </button>
                        @endif
                    @endcan

                    <button type="button" class="btn bg-primary-subtle text-primary" wire:click="export">
                        <i class="ri-file-excel-2-line"></i>
                        <span>@lang('translation.students.export')</span>
                    </button>

                    <button type="button" class="gap-1 btn bg-primary-subtle text-primary"
                        wire:click="toggleFilterVisibility">
                        <i class="{{ $showFilters ? 'ri-filter-off-line' : 'ri-filter-3-line' }}"></i>
                        <span>@lang('translation.filter')</span>
                    </button>
                </div>
            </div>

            <div class="flex-wrap gap-2 mb-3 d-flex align-items-center filter-section {{ $showFilters ? 'show' : '' }}"
                id="filterSection">
                <div class="filter-grid">
                    <div class="filter-item" wire:ignore>
                        <select id="teacher-select" class="form-control form-select">
                            <option value="">{{ __('translation.users.select_teacher') }}</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}"
                                    {{ $teacher->id == $this->teacher ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-item" wire:ignore>
                        <select id="group-select" class="form-control form-select">
                            <option value="">{{ __('translation.groups.select_group') }}</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}"
                                    {{ $group->id == $this->group ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-item">
                        <select id="creator" class="form-control form-select"
                            wire:change="filterByCreator($event.target.value)">
                            <option value="all">{{ __('translation.payments.created_by') }}</option>
                            @foreach ($creators as $creator)
                                <option value="{{ $creator->id }}"
                                    {{ $creator->id == $this->creator ? 'selected' : '' }}>
                                    {{ $creator->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-item">
                        <button type="button" class="bg-white border btn w-100" id="daterange-btn-debts"
                            data-livewire="@this">
                            <i class="far fa-calendar-alt"></i>
                            <span>
                                {{ $date ? $date : 'Select' }}
                            </span>
                            <i class="fas fa-caret-down"></i>
                        </button>
                    </div>

                    <div class="filter-item" wire:ignore>
                        <select id="studentGroupPaymentRange-select" class="form-control form-select">
                            <option value="all">{{ __('translation.select_range') }}</option>
                            @foreach ($studentGroupPaymentRanges as $range)
                                <option value="{{ $range }}">{{ $range }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('payment-card-data')
        @if ($showCards)
            @include('livewire.payment.partials.payment-type-cards')
        @endif
    @endcan

    <div class="card rounded-4">
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <tr>
                    <th>#</th>
                    <th>{{ __('translation.students.full_name') }}</th>
                    <th>{{ __('translation.students.phone') }}</th>
                    <th>{{ __('translation.students.group') }}</th>
                    <th>{{ __('attr.payment_type') }}</th>
                    <th>{{ __('attr.amount') }}</th>
                    <th>{{ __('attr.discount') }}</th>
                    <th>{{ __('attr.payment_date') }}</th>
                    <th></th>
                </tr>
                @forelse($payments as $payment)
                    <tr>
                        <td class="text-nowrap">
                            {{ $payments->total() - (($payments->currentpage() - 1) * $payments->perpage() + $loop->index) }}
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.students.show', $payment->student->id ?? 0) }}"
                                class="text-reset">
                                @if ($payment->student?->deleted_at)
                                    <del data-bs-toggle="tooltip"
                                        title="{{ __('translation.students.deleted') }}">{{ $payment->student?->getFullName() ?? '-' }}</del>
                                @else
                                    {{ $payment->student?->getFullName() ?? '-' }}
                                @endif
                            </a>
                        </td>
                        <td class="text-nowrap">
                            @if ($payment->student?->deleted_at)
                                <del data-bs-toggle="tooltip"
                                    title="{{ __('translation.students.deleted') }}">{{ $payment->student?->getFormattedPhone() ?? '-' }}</del>
                            @else
                                <a href="tel:{{ $payment->student?->getFormattedPhone() ?? '-' }}" class="text-reset">
                                    {{ $payment->student?->getFormattedPhone() ?? '-' }}
                                </a>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if (!$payment->student_group_payment?->group)
                                <span class="badge bg-success-subtle text-success">
                                    @lang('translation.students.payment_for_balance')
                                </span>
                            @endif
                            <a href="{{ route('admin.groups.show', $payment->student_group_payment->group->id ?? 0) }}"
                                class="text-reset">
                                {{ $payment->student_group_payment?->group?->name }}
                            </a>
                            <br>
                            <small class="text-muted">
                                {{ $payment->student_group_payment?->range }}
                            </small>
                        </td>
                        <td class="text-nowrap">
                            {{ $payment->payment_type?->name ?? '-' }}<br>
                            @if ($payment->refunds()->count())
                                <span class="badge bg-danger-subtle text-danger">
                                    @lang('translation.students.refunded')
                                </span>
                            @endif
                        </td>
                        <td class="text-nowrap fw-bold text-success">{{ money($payment->amount) }}</td>
                        <td @class([
                            'text-nowrap fw-bold',
                            'text-danger' => $payment->payment_discounts()->sum('amount') > 0,
                            'text-muted' => $payment->payment_discounts()->sum('amount') == 0,
                        ])>
                            {{ money($payment->payment_discounts()->sum('amount')) }}
                        </td>
                        <td class="text-nowrap">
                            <span class="fw-bold">
                                {{ dateFormat($payment->date, 'Y.m.d H:i') }}
                            </span><br>
                            <a href="{{ route('admin.users.show', $payment->creator->id ?? 0) }}" class="text-muted">
                                @lang('attr.receiver'): {{ $payment->creator?->name ?? '-' }}
                            </a>
                        </td>
                        <td style="width: 50px">
                            <div class="d-flex align-items-center">
                                <button class="btn text-muted t-show-btn" data-bs-toggle="modal"
                                    data-bs-target="#paymentModal" wire:click="openPayment({{ $payment->id }})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @can('payment-index')
                                    <form action="{{ route('admin.payments.send-sms', $payment->id) }}" method="POST">
                                        @csrf
                                        <button class="btn text-muted send-sms-btn"
                                            onclick="return confirm('Are you sure?')">
                                            <i class="ri-message-2-line fs-5"></i>
                                        </button>
                                    </form>
                                @endcan
                                @can('payment-edit')
                                    <a href="{{ route('admin.payments.edit', $payment) }}" class="btn edit-payment-btn">
                                        <i class="fa-solid fa-edit text-muted"></i>
                                    </a>
                                @endcan
                                @if ($payment->refunds()->count() == 0)
                                    @can('payment-refund')
                                        <a href="{{ route('admin.payments.refund', $payment->id) }}"
                                            class="btn text-muted refund-payment-btn">
                                            <i class="fa-solid fa-arrow-rotate-left"></i>
                                        </a>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100" class="text-center">
                            <x-no-result />
                        </td>
                    </tr>
                @endforelse
            </table>
            {{ $payments->links() }}
        </div>
        <div class="card-preloader d-none" wire:loading.class.remove="d-none">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3 d-flex justify-content-end">
        <div>
            <label for="per-page">
                @lang('translation.students.per_page')
            </label>
            <select id="per-page" class="form-control form-select d-block w-100" wire:model.live="perPage">
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="250">250</option>
                <option value="500">500</option>
            </select>
        </div>
    </div>

    <!-- Payment modal -->
    <div wire:ignore.self class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="paymentModalLabel">@lang('translation.transaction')</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                @if ($paymentModalItem)
                    <div class="modal-body">
                        <table class="table">
                            <tr>
                                <th>ID</th>
                                <td>{{ $paymentModalItem->id }}</td>
                            </tr>
                            <tr>
                                <th>@lang('translation.students.student')</th>
                                <td>{{ $paymentModalItem->student?->getFullName() ?? '-' }}</td>
                            </tr>
                            @if ($paymentModalItem->student_group_payment?->group)
                                <tr>
                                    <th>@lang('translation.groups.group')</th>
                                    <td>
                                        <a
                                            href="{{ route('admin.groups.show', $paymentModalItem->student_group_payment->group->id ?? 0) }}">
                                            {{ $paymentModalItem->student_group_payment->group?->name ?? '-' }}
                                        </a><br>
                                        <span class="text-muted">@lang('translation.groups.course'): </span>
                                        {{ $paymentModalItem->student_group_payment->group->course?->name ?? '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('translation.groups.debt_range')</th>
                                    <td>{{ $paymentModalItem->student_group_payment->range ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('translation.groups.teacher')</th>
                                    <td>
                                        <a
                                            href="{{ route('admin.users.show', $paymentModalItem->student_group_payment->group->teacher_id ?? 0) }}">
                                            {{ $paymentModalItem->student_group_payment->group->teacher?->name ?? '-' }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('translation.payments.form_of_payment')</th>
                                    <td>{{ $paymentModalItem->payment_type?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('translation.payments.payment_type')</th>
                                    <td>{{ __('translation.payments.for_debt') }}</td>
                                @else
                                <tr>
                                    <th>@lang('translation.payments.payment_type')</th>
                                    <td>{{ __('translation.payments.for_balance') }}</td>
                            @endif
                            <tr>
                                <th>@lang('translation.payments.form_of_payment')</th>
                                <td>{{ $paymentModalItem->payment_type?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>@lang('translation.payments.created_by')</th>
                                <td>
                                    <a href="{{ route('admin.users.show', $paymentModalItem->creator->id ?? 0) }}">
                                        {{ $paymentModalItem->creator->name ?? '-' }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('translation.students.payment_amount')</th>
                                <td>{{ money($paymentModalItem->amount ?? 0) }}</td>
                            </tr>
                            <tr>
                                <th>@lang('translation.payments.comment')</th>
                                <td>{{ $paymentModalItem->comment ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>@lang('attr.payment_date')</th>
                                <td>{{ dateFormat($paymentModalItem->created_at) }}</td>
                            </tr>
                            @if ($paymentModalItem?->refunds()->count())
                                <tr>
                                    <th>@lang('translation.payments.refund_comment')</th>
                                    <td>
                                        @foreach ($paymentModalItem->refunds as $refund)
                                            {{ $refund?->comment ?? '-' }}<br>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            @lang('translation.Close')
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="printPayment">
                            <i class="fas fa-print"></i>
                            Print
                        </button>
                    </div>
                @else
                    <div class="modal-body">
                        <div class="text-center p-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">@lang('translation.loading')</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            @lang('translation.Close')
                        </button>
                        <button type="button" class="btn btn-primary" disabled>
                            <i class="fas fa-print"></i>
                            Print
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('custom-scripts')
        <script type="text/javascript" src="{{ asset('assets/js/moment.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('assets/js/daterangepicker.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            // Initialize Select2
            $('#teacher-select').select2({
                placeholder: "{!! __('translation.all_teachers') !!}",
                allowClear: true,
                width: '100%',
            }).on('change', function(e) {
                let value = $(this).val();
                if (!value) {
                    value = '';
                }
                @this.filterByTeacher(value);
            });

            // Initialize Group Select2
            $('#group-select').select2({
                placeholder: "{!! __('translation.all_groups') !!}",
                allowClear: true,
                width: '100%',
            }).on('change', function(e) {
                let value = $(this).val();
                if (!value) {
                    value = 'all';
                }
                @this.filterByGroup(value);
            });

            // Initialize StudentGroupPayment Range Select2
            $('#studentGroupPaymentRange-select').select2({
                placeholder: "{!! __('translation.select_range') !!}",
                allowClear: true,
                width: '100%',
            }).on('change', function(e) {
                let value = $(this).val();
                if (!value) {
                    value = 'all';
                }
                @this.filterByStudentGroupPaymentRange(value);
            });

            // Set initial value if range is selected
            @if ($studentGroupPaymentRange && $studentGroupPaymentRange !== 'all')
                $('#studentGroupPaymentRange-select').val('{{ $studentGroupPaymentRange }}').trigger('change.select2');
            @endif

            // Set initial value if teacher is selected
            @if ($teacher && $teacher !== 'all')
                $('#teacher-select').val('{{ $teacher }}').trigger('change.select2');
            @endif

            // Set initial value if group is selected
            @if ($group && $group !== 'all')
                $('#group-select').val('{{ $group }}').trigger('change.select2');
            @endif

            //set locale moment
            moment.locale('{{ app()->getLocale() == 'ru' ? 'ru' : 'uz-latn' }}');

            // Parse the current date from Livewire component
            let currentDate = '{{ $date }}';
            let [startDate, endDate] = currentDate.split(' - ').map(date => moment(date, 'YYYY.MM.DD'));

            // Set the date range text initially
            $('#daterange-btn-debts span').html(currentDate);

            //Date range as a button
            $('#daterange-btn-debts').daterangepicker({
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
                    startDate: startDate,
                    endDate: endDate,
                    locale: {
                        "customRangeLabel": "{{ __('attr.optionalDate') }}",
                        "applyLabel": "{{ __('attr.save') }}",
                        "cancelLabel": "{{ __('attr.cancel') }}",
                    }
                },
                function(start, end) {
                    let selected = start.format('YYYY.MM.DD') + ' - ' + end.format('YYYY.MM.DD');
                    eval($('#daterange-btn-debts').attr('data-livewire')).set('date', selected);
                    $('#daterange-btn-debts span').html(selected);
                }
            );

            // Listen for Livewire updates
            Livewire.on('filterUpdated', () => {
                // Reset Select2 when filters are updated from Livewire
                if (@this.teacher === 'all') {
                    $('#teacher-select').val('').trigger('change.select2');
                }
                if (@this.group === 'all') {
                    $('#group-select').val('').trigger('change.select2');
                }
                if (@this.studentGroupPaymentRange === 'all') {
                    $('#studentGroupPaymentRange-select').val('').trigger('change.select2');
                }
            });

            Livewire.on('openPrintWindow', (params) => {
                try {
                    // Get modal element and instance
                    const modalEl = document.getElementById('paymentModal');
                    if (!modalEl) return;

                    // Get the Bootstrap modal instance
                    const modal = bootstrap.Modal.getInstance(modalEl);

                    // Close modal if it exists
                    if (modal) {
                        // First hide the modal
                        modal.hide();
                    }

                    // Clean up modal artifacts after a slight delay (for animation)
                    setTimeout(() => {
                        // Remove backdrop
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());

                        // Fix body
                        document.body.classList.remove('modal-open');
                        document.body.style.removeProperty('padding-right');

                        // Open the print window only after modal is fully closed
                        if (params && params.url) {
                            window.open(params.url, '_blank');
                        } else if (Array.isArray(params) && params.length > 0) {
                            window.open(params[0], '_blank');
                        }
                    }, 200);
                } catch (error) {
                    console.error('Error handling print window:', error);
                }
            });

            tippy('.send-sms-btn', {
                content: "{!! __('translation.payments.send_sms') !!}",
            });
            tippy('.edit-payment-btn', {
                content: "{!! __('translation.payments.edit') !!}",
            });
            tippy('.refund-payment-btn', {
                content: "{!! __('translation.payments.refund') !!}",
            });
        </script>
    @endpush

    @push('scripts')
        <script>
            // Date range picker initialization
            $('#daterange-btn-debts').daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
                        'month')]
                },
                startDate: moment().subtract(29, 'days'),
                endDate: moment(),
                locale: {
                    format: 'DD.MM.YYYY'
                }
            }, function(start, end) {
                $('#daterange-btn-debts span').html(start.format('DD.MM.YYYY') + ' - '
                    end.format('DD.MM.YYYY'));
                let livewire = document.querySelector("[data-livewire]").getAttribute('data-livewire');
                eval(livewire).set('date', start.format('DD.MM.YYYY') + ' - '
                    end.format('DD.MM.YYYY'));
            });

            // Select2 initialization
            $('#teacher-select').select2({
                theme: 'bootstrap-5'
            }).on('change', function() {
                @this.set('teacher', $(this).val());
            });

            $('#group-select').select2({
                theme: 'bootstrap-5'
            }).on('change', function() {
                @this.set('group', $(this).val());
            });
        </script>
    @endpush
</div>
