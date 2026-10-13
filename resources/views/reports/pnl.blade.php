@extends('layouts.master')
@section('title') PNL Reports @endsection
@section('css')
    <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('script-top')
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">PNL Reports</h5>
                        <div class="flex-shrink-0">
                            <form id="filterForm" method="GET" class="d-flex gap-2">
                                <select class="form-select" id="period" name="period" onchange="this.form.submit()">
                                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                @if($period === 'monthly')
                                    <input type="text" class="form-control" id="date" name="date" data-provider="flatpickr" 
                                           data-date-format="Y-m" value="{{ $date }}" onchange="this.form.submit()">
                                @else
                                    <input type="number" class="form-control" id="year" name="date" 
                                           value="{{ substr($date, 0, 4) }}" min="2000" max="2099" onchange="this.form.submit()">
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">Total Income</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-success">
                                ${{ number_format($totalIncome) }}
                            </h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-success rounded fs-3">
                                <i class="bx bx-dollar-circle text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">Total Expenses</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-danger">
                                ${{ number_format($totalExpenses) }}
                            </h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-danger rounded fs-3">
                                <i class="bx bx-shopping-bag text-danger"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">Net Profit</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                ${{ number_format($netProfit) }}
                            </h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title {{ $netProfit >= 0 ? 'bg-soft-success' : 'bg-soft-danger' }} rounded fs-3">
                                <i class="bx {{ $netProfit >= 0 ? 'bx-chart text-success' : 'bx-chart-down text-danger' }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Income by Category</h4>
                </div>
                <div class="card-body">
                    <div id="incomeChart"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Expenses by Category</h4>
                </div>
                <div class="card-body">
                    <div id="expenseChart"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Daily Income Trend</h4>
                </div>
                <div class="card-body">
                    <div id="trendChart"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Debug data
            console.log('Income Chart Data:', @json($incomeChartData));
            console.log('Expense Chart Data:', @json($expenseChartData));
            console.log('Trend Chart Data:', @json($trendChartData));

            // Income Chart
            var incomeChartOptions = {
                series: @json($incomeChartData['data']),
                chart: {
                    type: 'pie',
                    height: 350
                },
                labels: @json($incomeChartData['labels']),
                colors: ['#405189', '#299CDB', '#0AB39C', '#F7B84B', '#F06548'],
                legend: {
                    position: 'bottom'
                }
            };

            // Only render if we have data
            if (incomeChartOptions.series.length > 0) {
                var incomeChart = new ApexCharts(document.querySelector("#incomeChart"), incomeChartOptions);
                incomeChart.render();
            }

            // Expense Chart
            var expenseChartOptions = {
                series: @json($expenseChartData['data']),
                chart: {
                    type: 'pie',
                    height: 350
                },
                labels: @json($expenseChartData['labels']),
                colors: ['#405189', '#299CDB', '#0AB39C', '#F7B84B', '#F06548'],
                legend: {
                    position: 'bottom'
                }
            };

            // Only render if we have data
            if (expenseChartOptions.series.length > 0) {
                var expenseChart = new ApexCharts(document.querySelector("#expenseChart"), expenseChartOptions);
                expenseChart.render();
            }

            // Trend Chart
            var trendChartOptions = {
                series: [{
                    name: 'Daily Income',
                    data: @json($trendChartData['data'])
                }],
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                xaxis: {
                    type: 'datetime',
                    categories: @json($trendChartData['labels'])
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return value.toFixed(2);
                        }
                    }
                },
                tooltip: {
                    x: {
                        format: 'yyyy-MM-dd'
                    }
                },
                colors: ['#0AB39C'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    }
                }
            };

            // Only render if we have data
            if (trendChartOptions.series[0].data.length > 0) {
                var trendChart = new ApexCharts(document.querySelector("#trendChart"), trendChartOptions);
                trendChart.render();
            }
        });
    </script>
@endsection
