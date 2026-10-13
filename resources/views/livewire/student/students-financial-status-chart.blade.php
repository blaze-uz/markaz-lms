<div class="card card-height-100 rounded-4">
    <div class="p-0 card-body">
        <div id="studentsFinancialChart"></div>
    </div>
    @push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
                let studentsFinancialChart = new ApexCharts(document.querySelector("#studentsFinancialChart"), {
                    // Chart options
                    chart: {
                        type: 'pie',
                        height: 300,
                    },
                    plotOptions: {
                        pie: {
                            dataLabels: {
                                offset: -10,
                            },
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        offset: -30,
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
                        text: "{!!__('translation.students.financial_status')!!}",
                        align: 'center',
                        margin: 20,
                        offsetY: 3,
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '14px',
                        offsetY: -5,
                        itemMargin: {
                            horizontal: 10,
                            vertical: 5
                        },
                    },
                });

                studentsFinancialChart.render();
            });
    </script>
    @endpush
</div>