<div>
    @push('css')
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <style>
            .sticky-column {
                position: sticky;
                top: 130px;
            }

            .sticky-column .card {
                margin-bottom: 1rem;
            }

            .compact-metric-card .card-body {
                padding: 0.75rem;
            }

            .compact-metric-card .card-animate {
                transition: transform 0.2s;
            }

            .compact-metric-card .card-animate:hover {
                transform: translateY(-2px);
            }
        </style>
    @endpush
    {{-- <div class="row"> --}}
    <!-- Left Column - 4 cols -->
    {{-- <div class="col-lg-4">
            <div class="sticky-column"> --}}
    <!-- Line Chart -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card">

                <div class="card-body py-2" style="height: 170px; position: relative;" wire:ignore>
                    <canvas id="smsLineChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-2">
        <div class="col-lg col-md-6 col-sm-12 compact-metric-card">
            <div class="overflow-hidden border-0 card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-1 text-uppercase fw-medium text-muted fs-11">
                                @lang('translation.sms.sent_today')</p>
                            <h4 class="mb-0 fs-20 fw-semibold ff-secondary">{{ $card['sent_today'] }}</h4>
                        </div>
                        <div class="flex-shrink-0 avatar-xs">
                            <div class="avatar-title bg-success-subtle text-success rounded-circle fs-18">
                                <i class="ri-send-plane-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-6 col-sm-12 compact-metric-card">
            <div class="overflow-hidden border-0 card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-1 text-uppercase fw-medium text-muted fs-11">
                                @lang('translation.sms.pending')</p>
                            <h4 class="mb-0 fs-20 fw-semibold ff-secondary">{{ $card['pending'] }}</h4>
                        </div>
                        <div class="flex-shrink-0 avatar-xs">
                            <div class="avatar-title bg-warning-subtle text-warning rounded-circle fs-18">
                                <i class="ri-time-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-6 col-sm-12 compact-metric-card">
            <div class="overflow-hidden border-0 card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-1 text-uppercase fw-medium text-muted fs-11">
                                @lang('translation.sms.failed')</p>
                            <h4 class="mb-0 fs-20 fw-semibold ff-secondary">{{ $card['failed'] }}</h4>
                        </div>
                        <div class="flex-shrink-0 avatar-xs">
                            <div class="avatar-title bg-danger-subtle text-danger rounded-circle fs-18">
                                <i class="ri-error-warning-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-6 col-sm-12 compact-metric-card">
            <div class="overflow-hidden border-0 card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-1 text-uppercase fw-medium text-muted fs-11">
                                @lang('translation.sms.success_rate')</p>
                            <h4 class="mb-0 fs-20 fw-semibold ff-secondary">{{ $card['success_rate'] }}%
                            </h4>
                        </div>
                        <div class="flex-shrink-0 avatar-xs">
                            <div class="avatar-title bg-info-subtle text-info rounded-circle fs-18">
                                <i class="ri-bar-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-6 col-sm-12 compact-metric-card">
            <div class="overflow-hidden border-0 card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-1 text-uppercase fw-medium text-muted fs-11">
                                @lang('translation.sms.total_cost')</p>
                            <h4 class="mb-0 fs-20 fw-semibold ff-secondary">{{ number_format($card['total_cost'], 0, '.', ' ') }}</h4>
                        </div>
                        <div class="flex-shrink-0 avatar-xs">
                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-18">
                                <i class="ri-money-dollar-circle-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{--
            </div>
        </div> --}}

    <!-- Right Column - 8 cols -->
    {{-- <div class="col-lg-8"> --}}
    @if (count($selectedUsers) > 0)
        <div class="mb-3 alert bg-primary-subtle text-primary">
            <i class="mdi mdi-information me-2"></i>
            @lang('translation.sms.you_can_delete_only_pending_sms')
        </div>
    @endif

    @if ($selectPage && !$selectAll && count($selected) > 0)
        <div class="mb-3 alert bg-info-subtle text-info d-flex justify-content-between align-items-center">
            <span>
                <i class="mdi mdi-information me-2"></i>
                <strong>{{ count($selected) }}</strong> @lang('translation.sms.selected_on_page')
            </span>
            <button type="button" wire:click="selectAllAcrossPages" class="btn btn-sm btn-info">
                <i class="mdi mdi-check-all me-1"></i>
                @lang('translation.sms.select_all_results', ['count' => $this->getTotalFilteredCount()])
            </button>
        </div>
    @endif

    @if ($selectAll)
        <div class="mb-3 alert bg-success-subtle text-success d-flex justify-content-between align-items-center">
            <span>
                <i class="mdi mdi-check-all me-2"></i>
                @lang('translation.sms.all_selected', ['count' => $this->getTotalFilteredCount()])
            </span>
            <button type="button" wire:click="clearSelection" class="btn btn-sm btn-outline-success">
                <i class="mdi mdi-close me-1"></i>
                @lang('translation.sms.clear_selection')
            </button>
        </div>
    @endif

    <div class="card">
        <div class="border-0 card-header">
            <div class="gap-3 d-flex align-items-center">
                <div class="flex-grow-1">
                    <form action="{{ route('admin.sms.send') }}" method="POST" class="d-inline-block">
                        @csrf
                        <button type="submit" class="btn btn-primary" @disabled(count($selectedUsers) === 0)>
                            <i class="mdi mdi-send me-1"></i>
                            @lang('translation.sms.send_pending_sms')
                        </button>
                    </form>
                </div>
                <div @class([
                    'd-none' => count($selected) === 0 && !$selectAll,
                    'd-flex align-items-center gap-2',
                ])>
                    <span class="badge bg-danger-subtle text-danger">
                        @if ($selectAll)
                            <i class="mdi mdi-check-all me-1"></i>
                            {{ $this->getTotalFilteredCount() }} selected (all)
                        @else
                            {{ count($selected) }} selected
                        @endif
                    </span>
                    <form action="{{ route('admin.sms.delete') }}" method="POST" class="d-inline-block sms-delete-form">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="ids" value="{{ $selectAll ? 'all' : implode(',', $selected) }}">
                        <input type="hidden" name="selectAll" value="{{ $selectAll ? '1' : '0' }}">
                        <input type="hidden" name="selectedStatus" value="{{ $selectedStatus }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button type="button" class="btn btn-soft-danger btn-icon sms-delete-btn">
                            <i class="ri-delete-bin-2-line"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.sms.return-to-pending') }}" method="POST" class="d-inline-block sms-return-form">
                        @csrf
                        <input type="hidden" name="ids" value="{{ $selectAll ? 'all' : implode(',', $selected) }}">
                        <input type="hidden" name="selectAll" value="{{ $selectAll ? '1' : '0' }}">
                        <input type="hidden" name="selectedStatus" value="{{ $selectedStatus }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button type="button" class="btn btn-soft-warning btn-icon sms-return-btn" data-bs-toggle="tooltip"
                            title="{{ __('translation.return_to_pending') }}" wire:ignore>
                            <i class="ri-arrow-go-back-line"></i>
                        </button>
                    </form>
                </div>
                <div class="gap-2 d-flex align-items-center">
                    <div>
                        <button type="button" wire:click="toggleFilters" class="btn btn-soft-info">
                            <i class="align-bottom ri-filter-3-line me-1"></i>
                            @lang('translation.filters')
                            @if ($selectedStatus || $search)
                                <span
                                    class="badge bg-info ms-1">{{ ($selectedStatus ? 1 : 0) + ($search ? 1 : 0) }}</span>
                            @endif
                        </button>
                        @if ($selectedStatus || $search)
                            <button type="button" wire:click="clearFilters" class="btn btn-soft-danger"
                                data-bs-toggle="tooltip" title="@lang('translation.clear')">
                                <i class="align-bottom ri-refresh-line"></i>
                            </button>
                        @endif
                    </div>
                    <button type="button" class="btn btn-light" id="sms-date-range" data-livewire="@this">
                        <i class="far fa-calendar-alt me-1"></i>
                        <span>{{ $date ? $date : 'Select' }}</span>
                    </button>
                    <button type="button" wire:click="export" class="btn btn-soft-success">
                        <i class="ri-file-excel-2-line align-bottom me-1"></i>
                        @lang('translation.export')
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <!-- Filters Section -->
                    <div class="filters" x-data x-show="$wire.showFilters" x-collapse x-cloak>
                        <div class="mb-2 border shadow-none card card-body">
                            <div class="flex-wrap gap-2 d-flex">
                                <!-- Status Filter -->
                                <div class="min-w-150">
                                    <label class="form-label text-muted fs-13">@lang('translation.status')</label>
                                    <select wire:model.live="selectedStatus" class="form-select form-select-sm">
                                        <option value="">@lang('translation.all_statuses')</option>
                                        @foreach ($this->getStatusOptions() as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Search -->
                                <div class="flex-grow-1 min-w-250">
                                    <label class="form-label text-muted fs-13">@lang('translation.search')</label>
                                    <div class="search-box">
                                        <input wire:model.live="search" type="text"
                                            class="form-control form-control-sm search"
                                            placeholder="@lang('translation.search_by_phone_or_message')">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-sm">
                    <thead>
                        <tr class="text-muted">
                            <th style="width: 40px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectPageCheckbox"
                                        wire:model.live="selectPage" />
                                </div>
                            </th>
                            <th>@lang('translation.sms.receiver_name')</th>
                            <th>@lang('translation.students.phone')</th>
                            <th>@lang('translation.sms.content')</th>
                            <th>@lang('attr.status')</th>
                            <th>@lang('translation.sms.send_at')</th>
                            <th style="width: 100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sms as $item)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="sms_{{ $item->id }}"
                                            wire:model.live="selected" value="{{ $item->id }}" />
                                    </div>
                                </td>
                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            {{ $item->smsable?->name . ' ' . $item->smsable?->surname }}
                                        </div>
                                    </div>
                                </td>
                                <td class="text-nowrap">{{ $item->phone }}</td>
                                <td>
                                    <div class="text-wrap" style="max-width: 200px;">
                                        <span data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $item->content }}">
                                            {{ Str::limit($item->content, 35) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-nowrap">
                                    <span @class([
                                        'badge',
                                        'bg-success-subtle text-success' => $item->status === 'sent',
                                        'bg-warning-subtle text-warning' => $item->status === 'pending',
                                        'bg-danger-subtle text-danger' => $item->status === 'failed',
                                        'bg-primary-subtle text-primary' => $item->status === 'sending',
                                        'bg-secondary-subtle text-secondary' => $item->status === 'waiting',
                                    ])>
                                        @if ($item->status === 'sent')
                                            <i class="mdi mdi-check"></i>
                                        @elseif($item->status === 'pending')
                                            <i class="mdi mdi-clock"></i>
                                        @elseif($item->status === 'waiting')
                                            <i class="mdi mdi-clock"></i>
                                        @elseif($item->status === 'failed')
                                            <i class="mdi mdi-close"></i>
                                        @elseif($item->status === 'sending')
                                            <i class="mdi mdi-loading mdi-spin"></i>
                                        @endif
                                        {{ __('translation.sms.' . $item->status) }}
                                    </span>
                                </td>
                                <td class="text-nowrap">{{ dateFormat($item->updated_at, 'd.m.Y H:i') }}</td>
                                <td class="text-end">
                                    <div class="gap-2 hstack justify-content-end">
                                        <a href="{{ route('admin.sms.show', $item) }}"
                                            class="btn btn-sm btn-icon btn-soft-primary" data-bs-toggle="tooltip"
                                            title="{{ __('translation.btn.show') }}">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                        @if ($item->status == 'pending')
                            <form action="{{ route('admin.sms.delete') }}" method="POST"
                                class="d-inline-block sms-single-delete-form">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="ids" value="{{ $item->id }}">
                                <button type="button" class="btn btn-sm btn-icon btn-soft-danger sms-single-delete-btn"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('translation.btn.delete') }}">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-no-result />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $sms->links() }}
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
    {{--
        </div> --}}
    {{-- </div> --}}

    @push('custom-scripts')
        <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            // Initialize daterangepicker
            function initDateRangePicker() {
                if (typeof $ !== 'undefined' && typeof $.fn.daterangepicker !== 'undefined' && typeof moment !== 'undefined') {
                    // Set locale moment
                    moment.locale('{{ app()->getLocale() == 'ru' ? 'ru' : 'uz-latn' }}');

                    // Date range as a button
                    const $dateRange = $('#sms-date-range');
                    if ($dateRange.length) {
                        $dateRange.daterangepicker({
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
                            startDate: moment(),
                            endDate: moment(),
                            locale: {
                                "customRangeLabel": "{{ __('attr.optionalDate') }}",
                                "applyLabel": "{{ __('attr.save') }}",
                                "cancelLabel": "{{ __('attr.cancel') }}",
                            }
                        }, function(start, end) {
                            let selected = start.format('YYYY.MM.DD') + ' - ' + end.format('YYYY.MM.DD');
                            const livewireComponent = eval($dateRange.attr('data-livewire'));
                            if (livewireComponent) {
                                livewireComponent.set('date', selected);
                            }
                            $dateRange.find('span').html(selected);
                        });
                    }
                }
            }

            // Initialize when DOM is ready
            $(document).ready(function() {
                initDateRangePicker();
            });

            // Re-initialize after Livewire updates
            document.addEventListener('livewire:initialized', function() {
                initDateRangePicker();
            });

            document.addEventListener('livewire:navigated', function() {
                initDateRangePicker();
            });

            // Confirmation for bulk delete
            $(document).on('click', '.sms-delete-btn', function(e) {
                e.preventDefault();
                const form = $(this).closest('.sms-delete-form');
                const selectAll = form.find('input[name="selectAll"]').val() === '1';
                const count = selectAll ?
                    "{{ $this->getTotalFilteredCount() }}" :
                    form.find('input[name="ids"]').val().split(',').length;

                if (confirm("{{ __('translation.sms.confirm_delete', ['count' => ':count:']) }}".replace(':count:', count))) {
                    form.submit();
                }
            });

            // Confirmation for bulk return to pending
            $(document).on('click', '.sms-return-btn', function(e) {
                e.preventDefault();
                const form = $(this).closest('.sms-return-form');
                const selectAll = form.find('input[name="selectAll"]').val() === '1';
                const count = selectAll ?
                    "{{ $this->getTotalFilteredCount() }}" :
                    form.find('input[name="ids"]').val().split(',').length;

                if (confirm("{{ __('translation.sms.confirm_return_to_pending', ['count' => ':count:']) }}".replace(':count:', count))) {
                    form.submit();
                }
            });

            // Confirmation for single delete
            $(document).on('click', '.sms-single-delete-btn', function(e) {
                e.preventDefault();
                const form = $(this).closest('.sms-single-delete-form');

                if (confirm("{{ __('translation.sms.confirm_single_delete') }}")) {
                    form.submit();
                }
            });

            // Chart.js initialization
            let smsChart = null;

            function initSmsChart(chartData) {
                const ctx = document.getElementById('smsLineChart');
                if (!ctx) return;

                // Destroy existing chart
                if (smsChart) {
                    smsChart.destroy();
                }

                smsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [
                            {
                                label: "{{ __('translation.sms.chart.sms_count') }}",
                                data: chartData.counts,
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                                borderWidth: 1.5,
                                tension: 0.3,
                                yAxisID: 'y',
                                pointRadius: 0,
                                pointHoverRadius: 4,
                                pointHitRadius: 10,
                            },
                            {
                                label: "{{ __('translation.sms.chart.total_price') }}",
                                data: chartData.prices,
                                borderColor: 'rgb(255, 99, 132)',
                                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                                borderWidth: 1.5,
                                tension: 0.3,
                                yAxisID: 'y1',
                                pointRadius: 0,
                                pointHoverRadius: 4,
                                pointHitRadius: 10,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        layout: {
                            padding: {
                                top: 0,
                                bottom: 0,
                                left: 5,
                                right: 5
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    boxWidth: 8,
                                    padding: 6,
                                    font: {
                                        size: 9
                                    },
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                enabled: true,
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 10,
                                titleFont: {
                                    size: 11
                                },
                                bodyFont: {
                                    size: 11
                                },
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            if (context.datasetIndex === 1) {
                                                label += new Intl.NumberFormat('uz-UZ').format(context.parsed.y) + ' so\'m';
                                            } else {
                                                label += new Intl.NumberFormat('uz-UZ').format(context.parsed.y);
                                            }
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    font: {
                                        size: 9
                                    },
                                    maxRotation: 45,
                                    minRotation: 0
                                },
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: "{{ __('translation.sms.chart.sms_count') }}",
                                    font: {
                                        size: 10
                                    }
                                },
                                ticks: {
                                    font: {
                                        size: 9
                                    },
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: "{{ __('translation.sms.chart.price') }}",
                                    font: {
                                        size: 10
                                    }
                                },
                                ticks: {
                                    font: {
                                        size: 9
                                    },
                                    callback: function(value) {
                                        return new Intl.NumberFormat('uz-UZ', {
                                            notation: 'compact',
                                            compactDisplay: 'short'
                                        }).format(value);
                                    }
                                },
                                grid: {
                                    drawOnChartArea: false,
                                }
                            }
                        }
                    }
                });
            }

            // Initialize chart on page load
            document.addEventListener('DOMContentLoaded', function() {
                const chartData = @json($chartData ?? ['labels' => [], 'counts' => [], 'prices' => []]);
                initSmsChart(chartData);
            });

            // Listen for chart updates from Livewire
            document.addEventListener('livewire:initialized', function() {
                const chartData = @json($chartData ?? ['labels' => [], 'counts' => [], 'prices' => []]);
                initSmsChart(chartData);

                Livewire.on('chartUpdated', (event) => {
                    initSmsChart(event.chartData);
                });

            });
        </script>
    @endpush
</div>
