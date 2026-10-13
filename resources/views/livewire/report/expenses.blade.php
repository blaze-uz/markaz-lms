<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class="card card-height-100">
            <div class="card-header">
                @lang("translation.reports.top_expenses") <span class="badge bg-danger-subtle text-danger rounded-pill">Top 5</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <th>@lang("translation.expenses.item_name")</th>
                            <th>@lang("translation.expenses.amount")</th>
                            <th></th>
                        </tr>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $loop->index + 1 }}</td>
                                <td>{{ $expense['item_name'] }}</td>
                                <td class="text-danger text-nowrap fw-semibold">{{ money($expense['total']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100">
                                    <x-no-result/>
                                </td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12">
        <div class="card card-height-100">
            <div class="card-header">
                @lang("translation.expenses.expenses")
            </div>
            <div class="card-body">
                <div id="expenses-chart"></div>
            </div>
        </div>
    </div>
    @push('script-bottom')
        <script>
            let expenseChartOptions = {
                series: [{
                    data: @json(array_values($chartData['data']))
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        columnWidth: '45%',
                        distributed: true,
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: @json(array_values($chartData['labels'])),
                    labels: {
                        style: {
                            fontSize: '12px'
                        }
                    }
                }
            };
            let expenseChart = new ApexCharts(document.querySelector("#expenses-chart"), expenseChartOptions);
            expenseChart.render();
        </script>
    @endpush
</div>
