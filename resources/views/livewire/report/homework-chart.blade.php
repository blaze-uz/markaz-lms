<div>
    @if($hasHomeworkData)
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>@lang('translation.groups.homework') @lang('translation.reports.chart')</span>
        </div>
        <div class="card-body">
            <div id="homeworkChart"></div>
        </div>
    </div>
    @push('script-bottom')
        <script>
            let homeworkOptions = {
                series: [{
                    name: '{{ __('translation.groups.homework') }} - {{ __('attr.completed') }}',
                    data: @json(array_values($chartData['series']['completed'])),
                    color: '#09b29c' // Success color
                }, {
                    name: '{{ __('translation.groups.homework') }} - {{ __('attr.not_completed') }}',
                    data: @json(array_values($chartData['series']['not_completed'])),
                    color: '#ffc107' // Warning color
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

            let homeworkChart = new ApexCharts(document.querySelector("#homeworkChart"), homeworkOptions);
            homeworkChart.render();
        </script>
    @endpush
    @endif
</div>
