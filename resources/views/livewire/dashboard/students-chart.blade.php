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
                    height: 250,
                    type: 'bar',
                    toolbar: {
                        show: false,
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
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
                    },
                    formatter: function (val) {
                        return val;
                    }
                },
                xaxis: {
                    categories: {!! json_encode($labels) !!},
                    position: 'top',
                    labels: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
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
                            return Math.abs(Math.round(val));
                        }
                    }

                },
                title: {
                    text: "{{__('translation.students.count')}}" + " - " + "{{date('Y')}}",
                    floating: true,
                    offsetY: 230,
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
