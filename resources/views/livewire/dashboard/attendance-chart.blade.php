<div>
    <div class="card rounded-4">
        <div class="card-body">
            <div id="dashboardAttendanceChart"></div>
        </div>
    </div>
    @push('script-bottom')
        <script>
            let attendanceOptions = {
                series: [{
                    name: '{{ __('attr.present') }}',
                    data: @json(array_values($chartData['series']['present'])),
                    color: '#40a0fc' // Pure success color
                }, {
                    name: '{{ __('attr.absent') }}',
                    data: @json(array_values($chartData['series']['absent'])),
                    color: '#f3b53e' // Pure danger color
                }],
                chart: {
                    type: 'bar',
                    height: 240,
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
                title: {
                    text: '{{ __('translation.students_attendance') }} ({{ __('translation.last_7_days') }})',
                    align: 'center',
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

            let dashboardAttendanceChart = new ApexCharts(document.querySelector("#dashboardAttendanceChart"),
                attendanceOptions);
            dashboardAttendanceChart.render();
        </script>
    @endpush
</div>
