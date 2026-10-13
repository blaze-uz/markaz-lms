<div>
    <div class="card rounded-4">
        <div class="p-0 card-body">
            <div class="d-flex justify-content-end">
                <form action="{{ route('admin.clear-cache') }}" method="POST">
                    @csrf
                    <input type="hidden" name="key" value="students_age_chart">
                    <button type="submit" class="p-3 border-0 bg-transparent"
                        data-bs-togle="tooltip" title="{{__('translation.refresh')}}">
                        <i class="fas fa-sync"></i>
                    </button>
                </form>
            </div>
            <div id="studentsAgeChart"></div>
        </div>
        @push('custom-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                    let studentsAgehart = new ApexCharts(document.querySelector("#studentsAgeChart"), {
                        // Chart options
                        chart: {
                            type: 'pie',
                            height: 400,
                        },
                        plotOptions: {
                            pie: {
                                dataLabels: {
                                    offset: -10,
                                },
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            offset: -30,
                            dropShadow: {
                                enabled: true,
                                top: 0,
                                left: 0,
                                opacity: 0.1
                            }
                        },
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
                        labels: {!!json_encode($labels)!!},
                        colors: ["#40a0fc", '#f3b53e', '#1abc9c', '#f1556c', '#e3e7ed'],
                        title: {
                            text: "{!!__('translation.students_age_chart')!!}",
                            align: 'center',
                            margin: 20,
                            offsetY: 3,
                        },
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center',
                            fontSize: '14px',
                            offsetY: -5,
                            itemMargin: {
                                horizontal: 10,
                                vertical: 5
                            },
                        },
                    });

                    studentsAgehart.render();
                });
        </script>
        @endpush
    </div>
</div>