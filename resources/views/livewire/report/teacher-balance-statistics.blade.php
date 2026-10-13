<div>
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="card card-height-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-inline-block">
                            @lang("translation.users.staff")
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
                                <th>@lang("translation.users.name")</th>
                                <th>@lang("attr.balance")</th>
                            </tr>
                            @php
                            $allUsersBalanceSum = 0;
                            @endphp
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="text-muted">
                                        {{ $user->name }}
                                    </a>
                                </td>
                                <td @class(['fw-semibold', 'text-danger'=> $user->account->getBalance() <
                                        0, 'text-success'=> $user->account->getBalance() > 0,
                                        ])>
                                        {{ money($user->account->getBalance()) }}
                                </td>
                            </tr>
                            @php
                            $allUsersBalanceSum += $user->account->getBalance();
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
                            <span @class(['fw-semibold', 'text-danger'=> $allUsersBalanceSum < 0, 'text-success'=>
                                    $allUsersBalanceSum > 0, ])>
                                    {{ money($allUsersBalanceSum) }}
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
                    @lang("translation.users.staff")
                </div>
                <div class="overflow-auto card-body">
                    <div id="teachersBalanceChart"></div>
                </div>
            </div>
        </div>
    </div>
    @push('script-bottom')
    <script>
        let staffBalanceChartOptions = {
                series: [{
                    data: @json(array_values($chartData['data']))
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        columnWidth: '45%',
                        distributed: true,
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: @json(array_values($chartData['labels'])),
                    labels: {
                        style: {
                            fontSize: '12px'
                        }
                    }
                }
            };
            let staffBalanceChart = new ApexCharts(document.querySelector("#teachersBalanceChart"), staffBalanceChartOptions);
            staffBalanceChart.render();
    </script>
    @endpush
</div>