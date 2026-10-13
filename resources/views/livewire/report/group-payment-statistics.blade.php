<div>
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="card card-height-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-inline-block">
                            @lang("translation.groups.groups")
                        </div>
                        <input class="form-control form-control-sm w-25" type="search"
                            placeholder="{{__('translation.search')}}" aria-label="Qidirish"
                            wire:model.live.debounce.500ms="search">
                    </div>
                </div>
                <div class="overflow-auto card-body" style="height: 400px">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th>ID</th>
                                <th>@lang("translation.groups.name")</th>
                                <th>@lang("attr.balance")</th>
                            </tr>
                            @php
                            $allGroupsBalanceSum = 0;
                            @endphp
                            @foreach($groups as $group)
                            <tr>
                                <td>{{ $group->id }}</td>
                                <td>
                                    <a href="{{ route('admin.groups.show', $group->id) }}" class="text-muted">
                                        {{ $group->name }}
                                    </a>
                                </td>
                                <td>
                                    {{ money($group->account?->balance ?? 0) }}
                                </td>
                            </tr>
                            @php
                            $allGroupsBalanceSum += $group->account?->balance ?? 0;
                            @endphp
                            @endforeach
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            @lang("translation.total")
                        </div>
                        <div>
                            <span @class(['fw-semibold', 'text-danger'=> $allGroupsBalanceSum < 0, 'text-success'=>
                                    $allGroupsBalanceSum > 0, ])>
                                    {{ money($allGroupsBalanceSum) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-preloader d-none" wire:loading.class.remove="d-none">
                    <div class="card-status">
                        <div class="spinner-border text-success">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-12" wire:ignore>
            <div class="card card-height-100">
                <div class="card-header">
                    @lang("translation.groups.groups")
                </div>
                <div class="overflow-auto card-body">
                    <div id="groupPaymentChart"></div>
                </div>
            </div>
        </div>
    </div>
    @push('script-bottom')
    <script>
        let groupPaymentChartOptions = {
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '16px',
                                    fontFamily: 'Helvetica, Arial, sans-serif',
                                    fontWeight: 700,
                                    color: undefined,
                                    offsetY: 0
                                },
                                value: {
                                    show: false,
                                }
                            }
                        }
                    }
                },
                series: @json(array_values($groupPaymentsChartData)),
                //remove series labels and add them to legend
                labels: @json(array_keys($groupPaymentsChartData)),
                legend: {
                    show: false,
                    position: 'bottom',
                    horizontalAlign: 'center',
                    verticalAlign: 'middle',
                    floating: false,
                    fontSize: '14px',
                    offsetX: 0,
                    offsetY: 0,
                },
                chart: {
                    type: 'donut',
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            let groupPaymentChart = new ApexCharts(document.querySelector("#groupPaymentChart"), groupPaymentChartOptions);
            groupPaymentChart.render();
    </script>
    @endpush
</div>