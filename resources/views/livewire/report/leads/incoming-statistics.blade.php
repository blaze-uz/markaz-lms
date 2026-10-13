<div class="card card-height-100">
    <div class="card-header">
        @lang("translation.reports.income_expense_chart")
    </div>
    <div class="card-body">
        <div id="leadsIncomingStatistics"></div>
    </div>
</div>
@push('script-bottom')
{{-- income and expense chart  --}}
<script>
    options = {
        chart: {
            type: 'bar'
        },
        plotOptions: {
            bar: {
                horizontal: true
            }
        },
        dataLabels: {
            enabled: true,
            textAnchor: "start",
            style: {
                colors: ["#fff"],
            },
            formatter: function(val, opt) {
                return opt.w.globals.labels[opt.dataPointIndex] + ":  " + val;
            },
            offsetX: 0,
            dropShadow: {
                enabled: false,
            },
        },

        yaxis: {
            labels: {
                show: false,
            },
        },
        series: [{
            data: @json(array_values($boards_data))
        }],
        tooltip: {
            theme: "dark",
            x: {
                show: false,
            },
            y: {
                title: {
                    formatter: function() {
                        return "";
                    },
                },
            },
        },
    }


    let Incomechart = new ApexCharts(document.querySelector("#leadsIncomingStatistics"), options);
    Incomechart.render();
</script>
@endpush