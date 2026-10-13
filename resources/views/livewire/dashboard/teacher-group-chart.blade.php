<div class="card">
    <div class="card-header">
        <div class="card-title">
            @lang("translation.dashboards.groups_chart")
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive p-3">
            <div id="groupChart"></div>
        </div>
    </div>
    @push('custom-scripts')
        <script src="{{ asset('assets/js/apex/apex.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let groupChart = new ApexCharts(document.querySelector("#groupChart"), {
                    // Chart options
                    chart: {
                        width: 500,
                        type: 'pie' // Set the appropriate chart type here (e.g., 'line', 'area', etc.)
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 330,
                                height: 450
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }],
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
                    labels: {!!json_encode($labels)!!}
                });

                groupChart.render();
            });
        </script>
    @endpush
</div>
