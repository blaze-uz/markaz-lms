@extends('layouts.master')
@section('title')
    {{ $wrapped['year'] }} Wrapped - {{ $wrapped['user_name'] }}
@endsection
@section('css')
    <link href="{{ asset('assets/libs/choices.js/choices.js.min.css') }}" rel="stylesheet" type="text/css">
    <style>
        .wrapped-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
        }

        .wrapped-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .wrapped-header h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .wrapped-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .year-selector {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
        }

        .year-selector:focus {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            box-shadow: none;
        }

        .year-selector option {
            background: #667eea;
            color: white;
        }

        .top-course-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        [data-bs-theme="dark"] .wrapped-container {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        [data-bs-theme="dark"] .stat-card {
            background: rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.1);
        }

        [data-bs-theme="dark"] .chart-container {
            background: rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.1);
        }
    </style>
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>{{ $wrapped['year'] }} Wrapped 🎉</h3>
        <form action="{{ route('admin.wrapped.show') }}" method="GET" class="d-inline">
            <select name="year" class="form-select year-selector" onchange="this.form.submit()">
                @foreach ($availableYears as $availableYear)
                    <option value="{{ $availableYear }}" {{ $availableYear == $currentYear ? 'selected' : '' }}>
                        {{ $availableYear }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="wrapped-container">
        <div class="wrapped-header">
            <h1>Your {{ $wrapped['year'] }} in Review</h1>
            <p>Hey {{ $wrapped['user_name'] }}, here's your year wrapped! 🚀</p>
        </div>

        @if ($wrapped['user_type'] === 'admin')
            @include('admin.wrapped.partials.admin-stats')
        @elseif ($wrapped['user_type'] === 'teacher')
            @include('admin.wrapped.partials.teacher-stats')
        @else
            @include('admin.wrapped.partials.student-stats')
        @endif
    </div>
@endsection
@section('script')
    <script src="{{ asset('assets/js/apex/apex.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (isset($wrapped['charts']) && !empty($wrapped['charts']))
                @foreach ($wrapped['charts'] as $chartName => $chartData)
                    @if (!empty($chartData))
                        initializeChart('{{ $chartName }}', @json($chartData));
                    @endif
                @endforeach
            @endif
        });

        function initializeChart(chartId, data) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const values = Object.values(data);
            const labels = months.slice(0, values.length);

            const options = {
                series: [{
                    name: chartId.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                    data: values
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: {
                        show: false
                    },
                    background: 'transparent'
                },
                colors: ['#ffffff'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.3,
                        stops: [0, 100]
                    }
                },
                xaxis: {
                    categories: labels,
                    labels: {
                        style: {
                            colors: '#ffffff'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#ffffff'
                        }
                    }
                },
                grid: {
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    strokeDashArray: 4
                },
                tooltip: {
                    theme: 'dark'
                }
            };

            const chart = new ApexCharts(document.querySelector(`#chart-${chartId}`), options);
            chart.render();
        }
    </script>
@endsection

