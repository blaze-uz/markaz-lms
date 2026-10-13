<div>
    <div class="card card-height-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>@lang('translation.reports.students_chart')</span>
            <button wire:click="exportToExcel" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i>
                @lang('translation.export')
            </button>
        </div>
        <div class="card-body">
            <div id="students-chart"></div>
        </div>
    </div>

    @push('script-bottom')
        <script>
            let studentsChartOptions = {
                series: [{
                    name: '{{ __('translation.students.students') }}',
                    data: @json(array_values($chartData['series']['students'])),
                    color: '#09b29c' // Pure success color
                }, {
                    name: '{{ __('translation.students.deleted_students') }}',
                    data: @json(array_values($chartData['series']['deleted_students'])),
                    color: '#f4b53f' // Pure warning color
                }],
                chart: {
                    height: 350,
                    type: 'area',
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: true
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
                    categories: @json(array_values($chartData['labels'])),
                },
                tooltip: {
                    x: {
                        format: 'dd/MM/yy'
                    },
                },
            };

            let studentsChart = new ApexCharts(document.querySelector("#students-chart"), studentsChartOptions);
            studentsChart.render();
        </script>
    @endpush
</div>
