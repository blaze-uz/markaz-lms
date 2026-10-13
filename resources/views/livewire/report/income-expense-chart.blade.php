<div>

    @push('custom-scripts')
        <script src="{{ asset('assets/js/apex/apex.min.js') }}"></script>
        <script>
            {{--document.addEventListener('livewire:initialized', () => {--}}
            {{--    let incomeExpenseChart = new ApexCharts(document.querySelector("#incomeExpenseChart"), {--}}
            {{--        series: [{--}}
            {{--            name: '{{__("attr.income")}}',--}}
            {{--            data: @json(array_values($chartData['income']))--}}
            {{--        }, {--}}
            {{--            name: '{{__("attr.expense")}}',--}}
            {{--            data: @json(array_values($chartData['expense']))--}}
            {{--        }],--}}
            {{--        chart: {--}}
            {{--            height: 400,--}}
            {{--            type: 'area',--}}
            {{--            toolbar: {--}}
            {{--                show: false // This line removes the top widgets--}}
            {{--            }--}}
            {{--        },--}}
            {{--        dataLabels: {--}}
            {{--            enabled: false--}}
            {{--        },--}}
            {{--        stroke: {--}}
            {{--            curve: 'smooth'--}}
            {{--        },--}}
            {{--        xaxis: {--}}
            {{--            type: 'datetime',--}}
            {{--            categories: @json(array_keys($chartData['income']))--}}
            {{--        },--}}
            {{--        tooltip: {--}}
            {{--            x: {--}}
            {{--                format: 'dd/MM/yy'--}}
            {{--            },--}}
            {{--        },--}}

            {{--        //success and warning colors--}}
            {{--        colors: ['#46cb85', '#ffbe0b']--}}
            {{--    });--}}
            {{--    incomeExpenseChart.render();--}}
            {{--    --}}{{--if (incomeExpenseChart !== null) {--}}
            {{--    --}}{{--    incomeExpenseChart.updateOptions({--}}
            {{--    --}}{{--        series: [{--}}
            {{--    --}}{{--            name: '{{__("attr.income")}}',--}}
            {{--    --}}{{--            data: @json(array_values($chartData['income']))--}}
            {{--    --}}{{--        }, {--}}
            {{--    --}}{{--            name: '{{__("attr.expense")}}',--}}
            {{--    --}}{{--            data: @json(array_values($chartData['expense']))--}}
            {{--    --}}{{--        }],--}}
            {{--    --}}{{--        xaxis: {--}}
            {{--    --}}{{--            type: 'datetime',--}}
            {{--    --}}{{--            categories: @json(array_keys($chartData['income']))--}}
            {{--    --}}{{--        },--}}
            {{--    --}}{{--    });--}}
            {{--    --}}{{--}--}}
            {{--});--}}
        </script>
    @endpush
</div>
