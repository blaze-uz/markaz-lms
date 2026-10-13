<div class="card">
    <div class="card-header">
        <div class="card-title">
            @lang("translation.leads.calls_statistics")
        </div>
    </div>
    <div class="card-body p-0">
        <div id="callsChart"></div>
    </div>
    @push('custom-scripts')
        <script src="{{ asset('assets/js/apex/apex.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let callsChart = new ApexCharts(document.querySelector("#callsChart"), {
                    // Chart options
                    chart: {
                        width: 377,
                        type: 'pie' // Set the appropriate chart type here (e.g., 'line', 'area', etc.)
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 330,
                                height: 350
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }],
                    noData: {
                        text: @json(__('translation.no_data')),
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
                    colors: ["#28a745", "#dc3545", "#ffc107", "#6c757d"]
                });

                callsChart.render();
            });
        </script>
    @endpush
</div>
