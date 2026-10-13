<div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap align-items-center mb-3">
                        <h4 class="card-title mb-0">@lang('translation.reports.products_report')</h4>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-sm btn-success" wire:click="exportReport">
                                <i class="mdi mdi-file-excel-outline me-1"></i> @lang('translation.export')
                            </button>
                        </div>
                    </div>

                    <!-- Date Range Filters Only -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">@lang('translation.start_date')</label>
                            <input type="text" class="form-control flatpickr-date" placeholder="YYYY-MM-DD"
                                wire:model.live="startDate">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">@lang('translation.end_date')</label>
                            <input type="text" class="form-control flatpickr-date" placeholder="YYYY-MM-DD"
                                wire:model.live="endDate">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">@lang('translation.actions')</label>
                            <button type="button" class="btn btn-secondary w-100" wire:click="resetFilters">
                                @lang('translation.reset_filters')
                            </button>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0 me-3">
                                            <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                <i class="mdi mdi-cube-outline"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-1">@lang('translation.total_products_sold')</p>
                                            <h4 class="mb-0">{{ number_format($totalStats['total_quantity'] ?? 0) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0 me-3">
                                            <div class="avatar-title bg-soft-success text-success rounded-circle">
                                                <i class="mdi mdi-currency-usd"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-1">@lang('translation.total_sales')</p>
                                            <h4 class="mb-0">{{ money($totalStats['total_sales'] ?? 0) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0 me-3">
                                            <div class="avatar-title bg-soft-info text-info rounded-circle">
                                                <i class="mdi mdi-shopping-outline"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-1">@lang('translation.products_analyzed')</p>
                                            <h4 class="mb-0">{{ number_format($totalStats['total_products'] ?? 0) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Monthly Sales Trend Chart -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <h5 class="card-title mb-3">@lang('translation.monthly_sales_trend')</h5>
                            <div class="chart-container" style="height: 350px;">
                                <canvas id="monthlySalesChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products Charts -->
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <h5 class="card-title mb-3">@lang('translation.top_products_by_quantity')</h5>
                            <div class="chart-container" style="height: 350px;">
                                <canvas id="topProductsQuantityChart"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <h5 class="card-title mb-3">@lang('translation.product_inventory_status')</h5>
                            <div class="chart-container" style="height: 350px;">
                                <canvas id="inventoryPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart instances
            let monthlySalesChart = null;
            let topProductsQuantityChart = null;
            let topProductsRevenueChart = null;
            let salesDistributionChart = null;
            let inventoryPieChart = null;
            
            // Initialize flatpickr
            initFlatpickr();
            
            // Initialize charts with delay to ensure elements are rendered
            setTimeout(initCharts, 500);
            
            // Handle Livewire updates
            document.addEventListener('livewire:update', function() {
                initFlatpickr();
                setTimeout(initCharts, 300);
            });
            
            function initFlatpickr() {
                document.querySelectorAll('.flatpickr-date').forEach(element => {
                    if (element._flatpickr) {
                        element._flatpickr.destroy();
                    }
                    
                    flatpickr(element, {
                        dateFormat: 'Y-m-d',
                        locale: 'uz',
                        onChange: function(selectedDates, dateStr, instance) {
                            element.dispatchEvent(new Event('input'));
                        }
                    });
                });
            }
            
            function initCharts() {
                try {
                    const chartColors = {
                        primary: '#556ee6',
                        success: '#34c38f',
                        warning: '#f1b44c',
                        danger: '#f46a6a',
                        info: '#50a5f1',
                        dark: '#343a40',
                        muted: '#74788d',
                        primaryLight: '#e0e7fd',
                        successLight: '#d1f1e9'
                    };
                    
                    const transparentColors = [
                        'rgba(85, 110, 230, 0.8)',
                        'rgba(52, 195, 143, 0.8)',
                        'rgba(241, 180, 76, 0.8)',
                        'rgba(244, 106, 106, 0.8)',
                        'rgba(80, 165, 241, 0.8)',
                        'rgba(52, 58, 64, 0.8)',
                        'rgba(116, 120, 141, 0.8)',
                        'rgba(156, 39, 176, 0.8)'
                    ];
                    
                    const borderColors = [
                        'rgba(85, 110, 230, 1)',
                        'rgba(52, 195, 143, 1)',
                        'rgba(241, 180, 76, 1)',
                        'rgba(244, 106, 106, 1)',
                        'rgba(80, 165, 241, 1)',
                        'rgba(52, 58, 64, 1)'
                    ];
                    
                    // Monthly Sales Trend Chart
                    const monthlySalesCtx = document.getElementById('monthlySalesChart');
                    if (monthlySalesCtx) {
                        if (monthlySalesChart) {
                            try {
                                monthlySalesChart.destroy();
                            } catch (e) {
                                console.error('Error destroying chart:', e);
                            }
                        }
                        
                        monthlySalesChart = new Chart(monthlySalesCtx, {
                            type: 'line',
                            data: {
                                labels: @js($monthlyTrend['labels'] ?? []),
                                datasets: [
                                    {
                                        label: '{{ __("translation.sales") }}',
                                        data: @js($monthlyTrend['sales'] ?? []),
                                        backgroundColor: chartColors.primaryLight,
                                        borderColor: chartColors.primary,
                                        borderWidth: 2,
                                        fill: true,
                                        tension: 0.4
                                    },
                                    {
                                        label: '{{ __("translation.quantity") }}',
                                        data: @js($monthlyTrend['quantities'] ?? []),
                                        backgroundColor: 'transparent',
                                        borderColor: chartColors.success,
                                        borderWidth: 2,
                                        borderDash: [5, 5],
                                        fill: false,
                                        tension: 0.4,
                                        yAxisID: 'y1'
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: '{{ __("translation.sales_amount") }}'
                                        },
                                        ticks: {
                                            callback: function(value) {
                                                return '$' + value.toLocaleString();
                                            }
                                        }
                                    },
                                    y1: {
                                        beginAtZero: true,
                                        position: 'right',
                                        title: {
                                            display: true,
                                            text: '{{ __("translation.quantity") }}'
                                        },
                                        grid: {
                                            drawOnChartArea: false
                                        }
                                    }
                                }
                            }
                        });
                    }
                    
                    // Top Products By Quantity Chart
                    const topProductsQuantityCtx = document.getElementById('topProductsQuantityChart');
                    if (topProductsQuantityCtx) {
                        if (topProductsQuantityChart) {
                            try {
                                topProductsQuantityChart.destroy();
                            } catch(e) {
                                console.error('Error destroying chart:', e);
                            }
                        }
                        
                        topProductsQuantityChart = new Chart(topProductsQuantityCtx, {
                            type: 'bar',
                            data: {
                                labels: @js($chartData['productLabels'] ?? []),
                                datasets: [{
                                    label: '{{ __("translation.quantity_sold") }}',
                                    data: @js($chartData['productQuantities'] ?? []),
                                    backgroundColor: transparentColors,
                                    borderColor: borderColors,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: 'y',
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                }
                            }
                        });
                    }
                    
                    // Inventory Pie Chart
                    const inventoryPieCtx = document.getElementById('inventoryPieChart');
                    if (inventoryPieCtx) {
                        if (inventoryPieChart) {
                            try {
                                inventoryPieChart.destroy();
                            } catch (e) {
                                console.error('Error destroying chart:', e);
                            }
                        }
                        
                        inventoryPieChart = new Chart(inventoryPieCtx, {
                            type: 'pie',
                            data: {
                                labels: @js($inventoryData['labels'] ?? []),
                                datasets: [{
                                    data: @js($inventoryData['counts'] ?? []),
                                    backgroundColor: @js($inventoryData['colors'] ?? []),
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.raw || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                                return `${label}: ${value} products (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                    
                } catch (e) {
                    console.error('Chart initialization error:', e);
                }
            }
        });
    </script>
    @endpush
</div>
