<div class="card card-height-100">
    <div class="card-header">
        @lang("translation.reports.debts_by_groups")
    </div>
    <div class="card-body" wire:ignore.self>
        <div id="groupDebtChart"></div>
    </div>
    @push('script-bottom')
        <script>
            let groupDebtChartOptions = {
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '16px',
                                    fontFamily: 'Helvetica, Arial, sans-serif',
                                    fontWeight: 700,
                                    color: undefined,
                                    offsetY: 0
                                },
                                value: {
                                    show: false,
                                }
                            }
                        }
                    }
                },
                series: @json(array_values($chartData)),
                //remove series labels and add them to legend
                labels: @json(array_keys($chartData)),
                legend: {
                    show: false,
                    position: 'bottom',
                    horizontalAlign: 'center',
                    verticalAlign: 'bottom',
                    floating: false,
                    fontSize: '14px',
                    offsetX: 0,
                    offsetY: 0,
                },
                chart: {
                    type: 'pie',
                    width: '80%'
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };
            document.addEventListener('livewire:init', () => {
                let groupDebtChart = new ApexCharts(document.querySelector("#groupDebtChart"), groupDebtChartOptions);
                groupDebtChart.render();
            });

        </script>
    @endpush
</div>
