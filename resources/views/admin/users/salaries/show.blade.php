@extends('layouts.master')
@section('title')
    {{__('translation.users.salary')}}
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <div class="card">
                <div class="card-body p-4">
                    <div>
                        <div class="d-flex justify-content-center mx-auto w-100">
                            <div style="
                                height: 100px;
                                border-radius: 50%;
                                width: 100px !important;
                                @if($item->getFirstMediaUrl("image"))
                                    background-image: url('{{ $item->getFirstMediaUrl("image") }}') !important;
                                @endif
                                background-size: cover;
                                background-position: center;
                                object-fit: contain">
                                @if(!$item->getFirstMediaUrl("avatar"))
                                    {!! Avatar::create($item->name)->setTheme('grayscale-light')->setDimension(100)->setFontSize(35)->toSvg() !!}
                                @else
                                    <img class="rounded-circle header-profile-user"
                                         src="@if($item->getFirstMediaUrl('avatar')) {{$item->getFirstMediaUrl('avatar')}} @endif"
                                         alt="Header Avatar"
                                         style="object-fit: cover; height: 100px; width: 100px">
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h5 class="mb-1">
                                <a href="{{ route('admin.users.show', $item->id) }}">
                                    {{ $item->name }}
                                </a>
                            </h5>
                            <p class="text-muted">@lang("attr.create_date") {{ dateFormat($item->created_at) }}</p>
                        </div>
                        <div class="table-responsive mb-3">
                            <table class="table mb-0 table-borderless">
                                <tbody>
                                <tr>
                                    <th><span class="fw-medium">@lang("translation.users.phone")</span>
                                    </th>
                                    <td>
                                        <a href="tel:{{ $item->phone }}">{{ $item->getFormattedPhone() }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <th><span class="fw-medium">@lang("translation.users.percentage")</span>
                                    </th>
                                    <td>
                                        {{ $item->percentage }}%
                                    </td>
                                </tr>
                                <tr>
                                    <th><span class="fw-medium">@lang("translation.account")</span></th>
                                    <td>
                                        <a href="{{ route('admin.accounts.show', $item->account_id) }}">
                                            {{ $item->account->name }}
                                            <i class="mdi mdi-open-in-new"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th><span class="fw-medium">@lang("translation.users.email")</span>
                                    </th>
                                    <td>{{ $item->email }}</td>
                                </tr>
                                <tr>
                                    <th><span class="fw-medium">@lang("translation.users.birthday")</span>
                                    </th>
                                    <td>{{ dateFormat($item->birthday) }}</td>
                                </tr>
                                <tr>
                                    <th>
                                        <span class="fw-medium">@lang("translation.users.branches")</span>
                                    </th>
                                    <td>
                                        @foreach($item->branches as $branch)
                                            <span class="badge bg-success-subtle text-success">
                                                {{ $branch->name }}
                                            </span>
                                        @endforeach
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-sm-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-medium text-muted mb-0">@lang("translation.users.calculated")</p>
                                    <h2 class="mt-4 ff-secondary fw-semibold">
                                        <span class="counter-value"
                                              data-target="{{ $salary->incomeTransactions->sum('amount') }}">0</span>
                                    </h2>
                                </div>
                                <div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                            <i class="fa-solid fa-calculator text-info"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-medium text-muted mb-0">@lang("translation.users.given")</p>
                                    <h2 class="mt-4 ff-secondary fw-semibold">
                                        <span class="counter-value"
                                              data-target="{{ $salary->expenseTransactions->sum('amount') }}">0</span>
                                    </h2>
                                </div>
                                <div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle rounded-circle fs-2">
                                            <i class="fa-regular fa-circle-check text-success"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{__('translation.users.calculated')}}</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-primary-subtle">
                            <tr>
                                <th>ID</th>
                                <th>{{__('translation.users.from_where')}}</th>
                                <th>{{__('translation.users.amount')}}</th>
                                <th>{{__('translation.users.salary_type')}}</th>
                                <th>{{__('translation.users.which_month')}}</th>
                                <th>{{__('translation.users.causer')}}</th>
                                <th>{{__('translation.users.date')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($salary->incomeTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->id }}</td>
                                    <td>{{ $transaction->from_account?->name ?? '-' }}</td>
                                    <td>{{ money($transaction->pivot->amount) }}</td>
                                    <td>
                                        @if($transaction->pivot->type == 'income')
                                            <span class="badge bg-info-subtle text-info">{{__('translation.users.calculated')}}</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success">{{__('translation.users.given')}}</span>
                                        @endif
                                    </td>
                                    <td>{{ dateFormat($transaction->pivot->created_at, 'F') }}</td>
                                    <td>{{ $transaction->pivot->causer?->name ?? 'Auto' }}</td>
                                    <td>{{ dateFormat($transaction->pivot->created_at) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100" class="text-center">{{__('translation.no_data')}}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{__('translation.users.given')}}</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-primary-subtle">
                            <tr>
                                <th>ID</th>
                                <th>{{__('translation.users.from_where')}}</th>
                                <th>{{__('translation.users.amount')}}</th>
                                <th>{{__('translation.users.salary_type')}}</th>
                                <th>{{__('translation.users.which_month')}}</th>
                                <th>{{__('translation.users.causer')}}</th>
                                <th>{{__('translation.users.date')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($salary->expenseTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->id }}</td>
                                    <td>{{ $transaction->from_account?->name ?? '-' }}</td>
                                    <td>{{ money($transaction->pivot->amount) }}</td>
                                    <td>
                                        @if($transaction->pivot->type == 'income')
                                            <span class="badge bg-info-subtle text-info">{{__('translation.users.calculated')}}</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success">{{__('translation.users.given')}}</span>
                                        @endif
                                    </td>
                                    <td>{{ dateFormat($transaction->pivot->created_at, 'F') }}</td>
                                    <td>{{ $transaction->pivot->causer?->name ?? 'N/A' }}</td>
                                    <td>{{ dateFormat($transaction->pivot->created_at) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100" class="text-center">{{__('translation.no_data')}}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection