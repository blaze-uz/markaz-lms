<div class="card card-height-100">
    <div class="card-header">
        @lang("translation.reports.income_expense_chart")
    </div>
    <div class="card-body">
        <div id="leadsFunnelChart"></div>
    </div>
</div>
@push('script-bottom')
{{-- income and expense chart  --}}
<script>
    var options = {
        chart: {
            type: 'bar',
            height: 350,
        },
        plotOptions: {
            bar: {
                horizontal: true,
                isFunnel: true,
            },
        },
        series: [{
            name: "Funnel Series",
            data: @json(array_values($collections_data)),
        }, ]
    };

    let chart = new ApexCharts(document.querySelector("#leadsFunnelChart"), options);
    chart.render();
</script>
@endpush