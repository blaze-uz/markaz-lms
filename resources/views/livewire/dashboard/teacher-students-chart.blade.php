<div>
    <h5 class="mb-3">
        {{ __("translation.students.count") }}
    </h5>
    <div class="card rounded-4">
        <div class="p-2 card-body">
            <div id="usersChart"></div>
        </div>
        @push('custom-scripts')
        <script src="{{ asset('assets/js/apex/apex.min.js') }}"></script>
        <script>
            var options = {
                    series: [{
                        name: "{{__('translation.students.count')}}",
                        data: {{json_encode($series)}}
                    }],
                    chart: {
                        height: 350,
                        type: 'bar',
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 10,
                            dataLabels: {
                                position: 'top', // top, center, bottom
                            },
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        offsetY: -20,
                        style: {
                            fontSize: '12px',
                            colors: ["#304758"]
                        }
                    },
                    xaxis: {
                        categories: {!! json_encode($labels) !!},
                        position: 'top',
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                        tooltip: {
                            enabled: true,
                        }
                    },
                    yaxis: {
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false,
                        },
                        labels: {
                            show: true,
                            formatter: function (val) {
                                return Math.abs(Math.round(val)) + " ta";
                            }
                        }
                    },
                    title: {
                        text: "{{__('translation.students.count')}}" + " - " + "{{date('Y')}}",
                        floating: true,
                        offsetY: 330,
                        align: 'center',
                        style: {
                            color: '#444'
                        }
                    }
                };

                var chart = new ApexCharts(document.querySelector("#usersChart"), options);
                chart.render();
        </script>
        @endpush
    </div>
</div>