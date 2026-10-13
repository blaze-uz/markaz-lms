<div class="card rounded-4">
    <div class="card-header rounded-top-4">
        <h4 class="card-title mb-0">@lang('translation.sms.statistics')</h4>
    </div>
    <div class="card-body">
        <div id="sms_statistics" class="apex-charts" dir="ltr"></div>
    </div>
</div>

@push('script-bottom')
    <script>
        var options = {
            chart: {
                type: 'line',
                height: 350,
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false // Disable zoom feature
                }
            },
            series: @json($series),
            xaxis: {
                categories: @json($dates),
                title: {
                    text: '@lang('translation.sms.date')'
                }
            },
            yaxis: {
                title: {
                    text: '@lang('translation.sms.count')'
                }
            },
            colors: ['#2ab57d', '#f1b44c', '#fd625e', '#4b38b3'],
            stroke: {
                curve: 'smooth',
                width: 2,
            },
            legend: {
                position: 'bottom',
            },
            grid: {
                borderColor: '#f1f1f1',
            }
        };

        var chart = new ApexCharts(document.querySelector("#sms_statistics"), options);
        chart.render();
    </script>
@endpush
