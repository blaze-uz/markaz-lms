<div>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                {{__('translation.students.count_per_month')}}
            </h4>
        </div>
        <div class="card-body p-0">
            <div id="studentsCountPerMonthLineChart"></div>
        </div>
    </div>
    {{-- Nothing in the world is as soft and yielding as water. --}}
    @push('custom-scripts')
        <script>
            var options = {
                series: [{
                    name: "{{__('translation.students.count_per_month')}}",
                    data: {{json_encode($series)}}
                }],
                chart: {
                    height: 350,
                    type: 'line',
                    zoom: {
                        enabled: false
                    },
                    animations: {
                        enabled: false
                    }
                },
                labels: [
                    "{{__('translation.months.january')}}",
                    "{{__('translation.months.february')}}",
                    "{{__('translation.months.march')}}",
                    "{{__('translation.months.april')}}",
                    "{{__('translation.months.may')}}",
                    "{{__('translation.months.june')}}",
                    "{{__('translation.months.july')}}",
                    "{{__('translation.months.august')}}",
                    "{{__('translation.months.september')}}",
                    "{{__('translation.months.october')}}",
                    "{{__('translation.months.november')}}",
                    "{{__('translation.months.december')}}",
                ],
                xaxis: {},
            };

            var studentsCountPerMonthLineChart = new ApexCharts(document.querySelector("#studentsCountPerMonthLineChart"), options);
            studentsCountPerMonthLineChart.render();
        </script>
    @endpush
</div>
