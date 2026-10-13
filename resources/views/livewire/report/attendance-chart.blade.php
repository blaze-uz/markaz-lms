<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>@lang('translation.reports.attendance_chart')</span>
            <button wire:click="exportToExcel" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i>
                @lang('translation.export')
            </button>
        </div>
        <div class="card-body">
            <div id="attendanceChart"></div>
        </div>
    </div>
    @push('script-bottom')
        <script>
            let attendanceOptions = {
                series: [{
                    name: '{{ __('attr.present') }}',
                    data: @json(array_values($chartData['series']['present'])),
                    color: '#09b29c' // Pure success color
                }, {
                    name: '{{ __('attr.absent') }}',
                    data: @json(array_values($chartData['series']['absent'])),
                    color: '#ef6547' // Pure danger color
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    stacked: true,
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: true
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        legend: {
                            position: 'bottom',
                            offsetX: -10,
                            offsetY: 0
                        },
                        yaxis: {
                            show: true,
                            labels: {
                                style: {
                                    colors: '#8e8da4'
                                }
                            }
                        }
                    }
                }],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        borderRadius: 10,
                        dataLabels: {
                            total: {
                                enabled: true,
                                style: {
                                    fontSize: '13px',
                                    fontWeight: 900
                                }
                            }
                        }
                    },
                },
                xaxis: {
                    type: 'datetime',
                    categories: @json(array_values($chartData['labels'])),
                },
                yaxis: {
                    show: true,
                    labels: {
                        style: {
                            colors: '#8e8da4'
                        }
                    }
                },
                legend: {
                    position: 'right',
                    offsetY: 40
                },
                fill: {
                    opacity: 1
                }
            };

            let attendanceChart = new ApexCharts(document.querySelector("#attendanceChart"), attendanceOptions);
            attendanceChart.render();
        </script>
    @endpush
</div>
