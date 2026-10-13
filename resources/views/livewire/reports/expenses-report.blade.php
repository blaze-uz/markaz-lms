<div>
    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/daterangepicker.min.css') }}" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="{{ asset('assets/js/apex/apex.min.js') }}"></script>
        <style>
            .select2-selection__clear {
                position: absolute !important;
                right: 20px !important;
                top: 50% !important;
                transform: translateY(-50%) !important;
            }

            .stat-card {
                transition: transform 0.2s ease-in-out;
            }

            .stat-card:hover {
                transform: translateY(-2px);
            }

            .chart-container {
                position: relative;
                height: 300px;
                margin-bottom: 2rem;
            }

            .trend-up {
                color: #dc3545;
            }

            .trend-down {
                color: #28a745;
            }

            .dashboard-card {
                border: none;
                border-radius: 16px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                transition: all 0.3s ease;
                background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                position: relative;
                overflow: hidden;
            }

            .dashboard-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
                background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .dashboard-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            }

            .dashboard-card:hover::before {
                opacity: 1;
            }

            .dashboard-icon {
                width: 40px;
                height: 40px;
                border-radius: 12px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 18px;
                transition: all 0.3s ease;
            }

            .dashboard-card:hover .dashboard-icon {
                transform: scale(1.1);
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            }

            @media (max-width: 767.98px) {
                .dashboard-card {
                    border-radius: 12px;
                    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
                }

                .dashboard-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
                }

                .dashboard-icon {
                    width: 32px;
                    height: 32px;
                    font-size: 14px;
                    border-radius: 8px;
                }

                .card-body {
                    padding: 0.75rem !important;
                }
            }

            @media (min-width: 768px) and (max-width: 1199.98px) {
                .dashboard-card:hover {
                    transform: translateY(-3px);
                }
            }
        </style>
    @endpush

    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-outline-primary" id="expenses-date-range" data-livewire="@this">
            <i class="far fa-calendar-alt me-1"></i>
            <span>{{ $date ? $date : 'Select' }}</span>
            <i class="fas fa-caret-down ms-1"></i>
        </button>
    </div>

    <div class="mb-4 row g-3">
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card h-100 rounded-4">
                <div class="card-body p-3 p-md-4">
                    <h6 class="mb-3 card-title text-muted">@lang('translation.expenses.expenses')</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ money($totalAmount) }}</h3>
                            <small class="text-muted">{{ number_format($totalExpenses) }} @lang('translation.expenses.expenses')</small>
                        </div>
                        <div class="dashboard-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month Card -->
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card h-100 rounded-4">
                <div class="card-body p-3 p-md-4">
                    <h6 class="mb-3 card-title text-muted">@lang('translation.this_month')</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ money($thisMonthAmount) }}</h3>
                            <small class="text-muted {{ $monthlyGrowth >= 0 ? 'trend-up' : 'trend-down' }}">
                                <i class="fas fa-arrow-{{ $monthlyGrowth >= 0 ? 'up' : 'down' }}"></i>
                                {{ abs(round($monthlyGrowth, 1)) }}%
                            </small>
                        </div>
                        <div class="dashboard-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Place Card -->
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card h-100 rounded-4">
                <div class="card-body p-3 p-md-4">
                    <h6 class="mb-3 card-title text-muted">@lang('translation.expenses.place_name')</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if ($topExpensePlace)
                                <h5 class="mb-0">{{ money($topExpensePlace['total']) }}</h5>
                                <small class="text-muted">{{ $topExpensePlace['name'] }}</small>
                            @else
                                <h6 class="mb-0 text-muted">@lang('translation.no_data')</h6>
                            @endif
                        </div>
                        <div class="dashboard-icon">
                            <i class="fas fa-store"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Category Card -->
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card h-100 rounded-4">
                <div class="card-body p-3 p-md-4">
                    <h6 class="mb-3 card-title text-muted">@lang('translation.expenses.top_expense')</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if ($topExpenseItem)
                                <h5 class="mb-0">{{ money($topExpenseItem['total']) }}</h5>
                                <small class="text-muted">{{ $topExpenseItem['name'] }}</small>
                            @else
                                <h6 class="mb-0 text-muted">@lang('translation.no_data')</h6>
                            @endif

                        </div>
                        <div class="dashboard-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="mb-4 row">
        <!-- Monthly Trends Chart -->
        <div class="col-lg-8">
            <div class="border-0 shadow-sm card rounded-4">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0">@lang('translation.monthly_sales_trend')</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <div id="expensesMonthlyChart"
                            wire:key="monthly-chart-{{ $monthlyData->count() }}-{{ $totalAmount }}"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Breakdown Chart -->
        <div class="col-lg-4">
            <div class="border-0 shadow-sm card rounded-4">
                <div class="card-header bg-transparent border-0">
                    {{-- <h6 class="mb-0">@lang('translation.overview')</h6> --}}
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="categoryChart"
                            wire:key="category-chart-{{ $categoryData->count() }}-{{ $totalAmount }}"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Expenses -->
    {{-- <div class="mb-4">
        <div class="border-0 shadow-sm card">
            <div class="card-header bg-transparent border-0">
                <h6 class="mb-0">@lang('translation.expenses.created_by')</h6>
            </div>
            <div class="card-body">
                @if ($recentExpenses->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach ($recentExpenses as $expense)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-receipt text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $expense->item->name ?? 'N/A' }}</h6>
                                    <small class="text-muted">
                                        {{ $expense->place->name ?? 'N/A' }} •
                                        {{ $expense->createdBy->name ?? 'N/A' }} •
                                        {{ \Carbon\Carbon::parse($expense->date)->format('M d, Y') }}
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold">{{ money($expense->quantity * $expense->price) }}</span>
                                <br>
                                <small class="text-muted">{{ $expense->quantity }} × {{ money($expense->price) }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">@lang('translation.no_data')</p>
                    </div>
                @endif
            </div>
        </div>
    </div> --}}

    @push('custom-scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

        <script>
            $(document).ready(function() {
                initSelect2();
                initDateRangePicker();
                initCharts();

                document.addEventListener('livewire:navigated', () => {
                    initSelect2();
                    initDateRangePicker();
                    initCharts();
                });

                document.addEventListener('livewire:initialized', () => {
                    initSelect2();
                    initDateRangePicker();
                    initCharts();
                });

                Livewire.on('filters-reset', () => {
                    $('#item-select').val(null).trigger('change');
                    $('#place-select').val(null).trigger('change');
                    $('#account-select').val(null).trigger('change');
                    $('#payment-type-select').val(null).trigger('change');
                    // reset range button text to today
                    let today = moment().format('YYYY.MM.DD') + ' - ' + moment().format('YYYY.MM.DD');
                    $('#expenses-date-range span').html(today);
                });

                // Listen for Livewire updates to reinitialize charts
                Livewire.on('$refresh', () => {
                    setTimeout(() => {
                        initCharts();
                    }, 100);
                });

                // Listen for specific charts-updated event
                Livewire.on('charts-updated', () => {
                    setTimeout(() => {
                        initCharts();
                    }, 100);
                });




                // Cleanup charts when component is unmounted
                Livewire.on('$destroy', () => {
                    if (monthlyChartInstance) {
                        monthlyChartInstance.destroy();
                        monthlyChartInstance = null;
                    }
                    if (categoryChartInstance) {
                        categoryChartInstance.destroy();
                        categoryChartInstance = null;
                    }
                });
            });

            let monthlyChartInstance = null; // legacy Chart.js not used for monthly
            let apexMonthlyChartInstance = null;
            let categoryChartInstance = null;

            function initSelect2() {
                // Initialize item select
                $('#item-select').select2({
                    placeholder: "@lang('translation.expenses.item_name')",
                    allowClear: true,
                    width: '100%',
                }).on('select2:select', function(e) {
                    @this.set('selectedItem', e.target.value);
                }).on('select2:clear', function(e) {
                    @this.set('selectedItem', '');
                });

                // Initialize place select
                $('#place-select').select2({
                    placeholder: "@lang('translation.expenses.place_name')",
                    allowClear: true,
                    width: '100%',
                }).on('select2:select', function(e) {
                    @this.set('selectedPlace', e.target.value);
                }).on('select2:clear', function(e) {
                    @this.set('selectedPlace', '');
                });

                // Initialize account select
                $('#account-select').select2({
                    placeholder: "@lang('translation.account')",
                    allowClear: true,
                    width: '100%',
                }).on('select2:select', function(e) {
                    @this.set('selectedAccount', e.target.value);
                }).on('select2:clear', function(e) {
                    @this.set('selectedAccount', '');
                });

                // Initialize payment type select
                $('#payment-type-select').select2({
                    placeholder: "@lang('translation.expenses.payment_type_id')",
                    allowClear: true,
                    width: '100%',
                }).on('select2:select', function(e) {
                    @this.set('selectedPaymentType', e.target.value);
                }).on('select2:clear', function(e) {
                    @this.set('selectedPaymentType', '');
                });

                // Set initial values if they exist
                if (@this.selectedItem) {
                    $('#item-select').val(@this.selectedItem).trigger('change');
                }
                if (@this.selectedPlace) {
                    $('#place-select').val(@this.selectedPlace).trigger('change');
                }
                if (@this.selectedAccount) {
                    $('#account-select').val(@this.selectedAccount).trigger('change');
                }
                if (@this.selectedPaymentType) {
                    $('#payment-type-select').val(@this.selectedPaymentType).trigger('change');
                }
            }

            function initDateRangePicker() {
                // set locale
                moment.locale('{{ app()->getLocale() == 'ru' ? 'ru' : 'uz-latn' }}');
                let currentDate = '{{ $date }}';
                let [startDate, endDate] = currentDate.split(' - ').map(date => moment(date, 'YYYY.MM.DD'));
                $('#expenses-date-range span').html(currentDate);
                $('#expenses-date-range').daterangepicker({
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
                        eval($('#expenses-date-range').attr('data-livewire')).set('date', selected);
                        $('#expenses-date-range span').html(selected);
                    }
                );
            }

            // Global function to reinitialize charts
            window.reinitializeCharts = function() {
                initCharts();
            };

            function initCharts() {

                // Destroy existing charts if they exist
                if (monthlyChartInstance) {
                    monthlyChartInstance.destroy();
                    monthlyChartInstance = null;
                }
                if (categoryChartInstance) {
                    categoryChartInstance.destroy();
                    categoryChartInstance = null;
                }

                // Monthly Trends Chart using ApexCharts (like admin.reports.partials.payment-chart)
                const monthlyEl = document.getElementById('expensesMonthlyChart');
                if (monthlyEl) {
                    const categories = @this.monthlyCategories || [];
                    const series = @this.monthlySeries || [];

                    if (apexMonthlyChartInstance) {
                        apexMonthlyChartInstance.destroy();
                        apexMonthlyChartInstance = null;
                    }

                    const options = {
                        series: series.length > 0 ? series : [{
                            name: '@lang('translation.expenses.expenses')',
                            data: [0]
                        }],
                        chart: {
                            height: 300,
                            type: 'area',
                            toolbar: {
                                show: false
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth'
                        },
                        xaxis: {
                            categories: categories.length > 0 ? categories : ['No Data']
                        },
                        colors: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#2E93fA', '#66DA26',
                            '#546E7A', '#E91E63'
                        ]
                    };

                    apexMonthlyChartInstance = new ApexCharts(monthlyEl, options);
                    apexMonthlyChartInstance.render();
                }

                // Category Breakdown Chart
                const categoryCtx = document.getElementById('categoryChart');
                if (categoryCtx) {
                    // Get fresh data from Livewire
                    const categoryData = @this.categoryData || [];
                    const categoryLabels = categoryData.map(item => item.name);
                    const categoryValues = categoryData.map(item => item.total);
                    categoryChartInstance = new Chart(categoryCtx, {
                        type: 'doughnut',
                        data: {
                            labels: categoryLabels.length > 0 ? categoryLabels : ['No Data'],
                            datasets: [{
                                data: categoryValues.length > 0 ? categoryValues : [1],
                                backgroundColor: [
                                    '#FF6384',
                                    '#36A2EB',
                                    '#FFCE56',
                                    '#4BC0C0',
                                    '#9966FF',
                                    '#FF9F40',
                                    '#FF6384',
                                    '#C9CBCF',
                                    '#4BC0C0',
                                    '#FF6384'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            }
        </script>
    @endpush
</div>
