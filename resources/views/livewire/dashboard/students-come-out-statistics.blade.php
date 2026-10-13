<div class="card card-height-100 rounded-4">
    <div class="card-body">
        <div id="line-adwords" class=""></div>
    </div>

    @push('custom-scripts')
        <script src="{{ asset('assets/js/apex/apex.min.js') }}"></script>
        <script>
            var optionsLine = {
                chart: {
                    height: 240,
                    type: 'line',
                    zoom: {
                        enabled: true
                    },
                    toolbar: {
                        show: false,
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                colors: ["#40a0fc", '#f3b53e'],
                series: [{
                        name: '{{ __('translation.comers') }}',
                        data: {!! json_encode($series['come']) !!}
                    },
                    {
                        // name: "Ketganlar",
                        name: '{{ __('translation.leavers') }}',
                        data: {!! json_encode($series['out']) !!}
                    }
                ],
                title: {
                    text: '{{ __('translation.students_flow') }}' + " - " + "{{ ucfirst(dateFormat(today(), 'F', 'ru')) }}",
                    align: 'bottom',
                    offsetY: 220,
                    offsetX: 20
                },
                markers: {
                    size: 6,
                    strokeWidth: 0,
                    hover: {
                        size: 9
                    }
                },
                grid: {
                    show: true,
                    padding: {
                        bottom: 0
                    }
                },
                dataLabels: {
                    enabled: false,
                },
                labels: {!! json_encode($labels) !!},
                xaxis: {
                    tooltip: {
                        enabled: false
                    },
                    position: 'top',
                    labels: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                },
                yaxis: {
                    show: true,
                    labels: {
                        style: {
                            colors: '#8e8da4'
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    offsetY: -20
                }
            }

            var chartLine = new ApexCharts(document.querySelector('#line-adwords'), optionsLine);
            chartLine.render();
        </script>
    @endpush
</div>
