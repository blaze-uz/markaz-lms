@extends('layouts.master')
@section('title')
    @lang('translation.accounts.accounts')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="mb-3 d-flex justify-content-end">
        {{-- <button type="button" class="btn btn-success add-btn disabled" disabled> --}}
        <a href="{{ route('admin.accounts.transfer') }}" class="btn btn-success">
            <i class="align-bottom ri-add-line me-1"></i>
            @lang('translation.transfers.add')
        </a>
        {{-- </button> --}}
    </div>
    {{-- in proccess text --}}
    {{-- <div class="alert alert-warning alert-dismissible fade show" role="alert"> --}}
    {{-- <i class="align-middle ri-alert-line me-2"></i> --}}
    {{-- Kechirasiz, bo'lim qayta ishlab chiqish jarayonida! --}}
    {{-- </div> --}}
    <div class="card">
        <div class="card-body">
            <!-- Nav tabs -->
            <ul class="mb-3 nav nav-tabs nav-justified" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#staff_accounts" role="tab"
                        aria-selected="true">
                        @lang('attr.staff_accounts')
                        <span @class([
                            'badge',
                            'bg-danger-subtle text-danger' => $staffAccountsSum < 0,
                            'bg-success-subtle text-success' => $staffAccountsSum >= 0,
                        ])>
                            {{ money($staffAccountsSum) }}
                        </span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="align-middle nav-link" data-bs-toggle="tab" href="#group_accounts" role="tab"
                        aria-selected="false" tabindex="-1">
                        @lang('attr.group_accounts')
                        <span @class([
                            'badge',
                            'bg-danger-subtle text-danger' => $groupAccountsSum < 0,
                            'bg-success-subtle text-success' => $groupAccountsSum >= 0,
                        ])>
                            {{ money($groupAccountsSum) }}
                        </span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="align-middle nav-link" data-bs-toggle="tab" href="#student_accounts" role="tab"
                        aria-selected="false" tabindex="-1">
                        @lang('attr.student_accounts')
                        <span @class([
                            'badge',
                            'bg-danger-subtle text-danger' => $studentAccountsSum < 0,
                            'bg-success-subtle text-success' => $studentAccountsSum >= 0,
                        ])>
                            {{ money($studentAccountsSum) }}
                        </span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#other_accounts" role="tab" aria-selected="false"
                        tabindex="-1">
                        @lang('attr.other_accounts')
                        <span @class([
                            'badge',
                            'bg-danger-subtle text-danger' => $otherAccountsSum < 0,
                            'bg-success-subtle text-success' => $otherAccountsSum >= 0,
                        ])>
                            {{ money($otherAccountsSum) }}
                        </span>
                    </a>
                </li>
            </ul>
            <!-- Nav tabs -->
            <div class="tab-content">
                <div class="tab-pane active show" id="staff_accounts" role="tabpanel">
                    <livewire:account.staff-accounts-table lazy />
                </div>
                <div class="tab-pane" id="group_accounts" role="tabpanel">
                    <livewire:account.group-accounts-table lazy />
                </div>
                <div class="tab-pane" id="student_accounts" role="tabpanel">
                    <livewire:account.student-accounts-table lazy />
                </div>
                <div class="tab-pane" id="other_accounts" role="tabpanel">
                    <livewire:account.other-accounts-table lazy />
                </div>
            </div>
        </div><!-- end card-body -->
    </div><!-- end card -->
@endsection
