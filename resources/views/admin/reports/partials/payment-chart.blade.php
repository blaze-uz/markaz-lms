<div class="card">
    <div class="card-header">
        @lang("translation.reports.income_expense_chart")
    </div>
    <div class="card-body">
        <div id="incomeExpenseChart"></div>
    </div>
</div>
@push('script-bottom')
    {{--  income and expense chart  --}}
    <script>
        var options = {
            series: [{
                name: '@lang("attr.income")',
                data: @json(array_values($chartData['income']))
            }, {
                name: '@lang("attr.expense")',
                data: @json(array_values($chartData['expense']))
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            xaxis: {
                type: 'datetime',
                categories: @json(array_keys($chartData['income'])),
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy'
                },
            },
            //success and warning colors
            colors: ['#46cb85', '#ffbe0b']
        };

        let chart = new ApexCharts(document.querySelector("#incomeExpenseChart"), options);
        chart.render();
    </script>
@endpush
