<div class="card card-height-100 rounded-4">
    <div class="p-2 card-body">
        <div id="genderChart"></div>
    </div>
    @push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
                let genderChart = new ApexCharts(document.querySelector("#genderChart"), {
                    // Chart options
                    chart: {
                        type: 'donut',
                        height: 300,
                    },
                    dataLabels: {
                        enabled: true,
                        dropShadow: {
                            enabled: true,
                            top: 0,
                            left: 0,
                            opacity: 0.1
                            }
                    },
                    noData: {
                        text: "{!!__('translation.no_data')!!}",
                        align: 'center',
                        verticalAlign: 'top',
                        offsetX: 0,
                        offsetY: 0,
                        style: {
                            color: '#323232',
                            fontSize: '14px',
                            fontFamily: 'Helvetica, Arial, sans-serif'
                        }
                    },
                    series: {!!json_encode($series)!!},
                    labels: {!!json_encode($labels)!!},
                    colors: ["#40a0fc", '#f3b53e'],
                    title: {
                        text: "{!!__('translation.students_chart_by_gender')!!}",
                        align: 'center',
                        margin: 10,
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '14px',
                        offsetY: -5,
                    },
                });
                genderChart.render();
            });
    </script>
    @endpush
</div>