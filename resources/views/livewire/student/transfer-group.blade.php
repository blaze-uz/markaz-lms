<div>
    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    @endpush
    {{--    <div class="row">--}}
    {{--        <div class="col-md-5 col-sm-12">--}}
    {{--            <div class="card">--}}
    {{--                <div class="card-header">--}}
    {{--                    <div class="card-title fs-16 fw-medium">--}}
    {{--                        @lang("translation.students.student")--}}
    {{--                    </div>--}}
    {{--                </div>--}}
    {{--                <div class="card-body">--}}
    {{--                    <div class="table-responsive">--}}
    {{--                        <table class="table">--}}
    {{--                            <tr>--}}
    {{--                                <th>ID</th>--}}
    {{--                                <td>{{ $student->id }}</td>--}}
    {{--                            </tr>--}}
    {{--                            <tr>--}}
    {{--                                <th>@lang("translation.students.full_name")</th>--}}
    {{--                                <td>--}}
    {{--                                    <a href="{{ route('admin.students.show', $student->id) }}" class="fw-medium">--}}
    {{--                                        {{ $student->getFullName() }}--}}
    {{--                                    </a>--}}
    {{--                                </td>--}}
    {{--                            </tr>--}}
    {{--                            <tr>--}}
    {{--                                <th>@lang("translation.students.phone")</th>--}}
    {{--                                <td>--}}
    {{--                                    <a href="tel:{{ $student->getFormattedPhone() }}" class="fw-medium">--}}
    {{--                                        {{ $student->getFormattedPhone() }}--}}
    {{--                                    </a>--}}
    {{--                                </td>--}}
    {{--                            </tr>--}}
    {{--                        </table>--}}
    {{--                    </div>--}}
    {{--                </div>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--        <div class="col-md-7 col-sm-12">--}}
    {{--            <div class="card card-height-100">--}}
    {{--                <div class="card-header">--}}
    {{--                    <div class="card-title fs-16 fw-medium">--}}
    {{--                        @lang("translation.students.debts")--}}
    {{--                    </div>--}}
    {{--                </div>--}}
    {{--                <div class="card-body">--}}
    {{--                    <div class="table-responsive">--}}
    {{--                        @if($selectedStudentGroupDebts->count())--}}
    {{--                            <table class="table">--}}
    {{--                                <tr>--}}
    {{--                                    <th>ID</th>--}}
    {{--                                    <th>@lang("translation.groups.debt_range")</th>--}}
    {{--                                    <th>@lang("attr.left_amount")</th>--}}
    {{--                                    <th>@lang("translation.groups.will_deleted")</th>--}}
    {{--                                </tr>--}}
    {{--                                @foreach($selectedStudentGroupDebts as $debt)--}}
    {{--                                    <tr>--}}
    {{--                                        <td>{{ $debt->id }}</td>--}}
    {{--                                        <td>{{ $debt->range }}</td>--}}
    {{--                                        <td>{{ money($debt->left_amount) }}</td>--}}
    {{--                                        <td>--}}
    {{--                                            <div class="mb-3 form-check form-switch">--}}
    {{--                                                <input type="checkbox" class="form-check-input"--}}
    {{--                                                       wire:click="selectDebt({{ $debt->id }})"--}}
    {{--                                                       value="{{ $debt->id }}">--}}
    {{--                                            </div>--}}
    {{--                                        </td>--}}
    {{--                                    </tr>--}}
    {{--                                @endforeach--}}
    {{--                            </table>--}}
    {{--                            <div class="col-12">--}}
    {{--                                <div class="alert bg-info-subtle text-info">--}}
    {{--                                    <i class="fa-solid fa-info-circle"></i>--}}
    {{--                                    @lang("translation.students.debt_info")--}}
    {{--                                </div>--}}
    {{--                            </div>--}}
    {{--                        @else--}}
    {{--                            <div class="col-12">--}}
    {{--                                @lang("translation.groups.no_debt")--}}
    {{--                            </div>--}}
    {{--                        @endif--}}
    {{--                    </div>--}}
    {{--                </div>--}}
    {{--                <div class="card-preloader d-none" wire:loading.class.remove="d-none">--}}
    {{--                    <div class="card-status">--}}
    {{--                        <div class="spinner-border text-success">--}}
    {{--                            <span class="visually-hidden">Loading...</span>--}}
    {{--                        </div>--}}
    {{--                    </div>--}}
    {{--                </div>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </div>--}}
    <div class="mt-3 mb-5 row d-flex justify-content-center">
        <div class="col-md-5 col-sm-12">
            
            <div class="card h-100">
                <div class="p-2 card-header">
                    <div class="mx-auto mb-1 d-flex justify-content-center" wire:ignore>
                        <select class="form-select group-select2" id="student-groups" name="group_id">
                            @foreach($studentGroups as $studentGroup)
                                <option value="{{ $studentGroup->id }}"
                                        @if($studentGroup->id == $selectedStudentGroup->id) selected @endif>
                                    {{ $studentGroup->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body box-profile">
                    <h5 class="mb-1">
                        <a href="{{ route('admin.groups.show', $selectedStudentGroup->id) }}" class="fw-medium">
                            {{ $selectedStudentGroup->name }}
                        </a>
                    </h5>
                    <p class="mb-0 text-muted">@lang("attr.create_date")
                        : {{ dateFormat($selectedStudentGroup->created_at) }}</p>
                    <ul class="my-3 list-group list-group-unbordered">
                        <li class="list-group-item d-flex justify-content-between">
                            <b>@lang("translation.groups.teacher")</b>
                            <a href="{{ route('admin.users.show', $selectedStudentGroup->teacher_id ?? 0) }}"
                               class="fw-bold">
                                {{ $selectedStudentGroup->teacher?->name ?? '-' }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <b> @lang("translation.groups.course")</b>
                            <a class="float-right">{{ $selectedStudentGroup->course->name ?? '-' }}</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <b>@lang("translation.groups.branch")</b>
                            <a class="float-right">{{ $selectedStudentGroup->branch->name ?? '-' }}</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <b>@lang("translation.groups.amount")</b>
                            <a class="float-right">
                                {{ money($selectedStudentGroup->amount) }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <b>@lang("translation.groups.monthly_payment_type")</b>
                            <a class="float-right">
                                {{ __('translation.groups.'.$selectedStudentGroup->payment_type) }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <b>@lang("translation.groups.start_date")</b>
                            <a class="float-right">
                                {{ dateFormat($selectedStudentGroup->start_date) }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <b>@lang("translation.groups.debt_range")</b>
                            <a class="float-right">
                                {{ $selectedStudentGroup->getPaymentDate() }}
                            </a>
                        </li>
                    </ul>
                    <hr>
                    <h5>
                        {{ __('translation.students.debts') }}
                    </h5>
                    <table class="table table-sm">
                        <tr>
                            <th>ID</th>
                            <th>@lang("translation.groups.debt_range")</th>
                            <th>@lang("attr.left_amount")</th>
                            <th>@lang("translation.groups.will_deleted")</th>
                        </tr>
                        @forelse($selectedStudentGroupDebts as $debt)
                            <tr>
                                <td>{{ $debt->id }}</td>
                                <td>{{ $debt->range }}</td>
                                <td>{{ money($debt->left_amount) }}</td>
                                <td>
                                    <div class="mb-3 form-check form-switch">
                                        <input type="checkbox" class="form-check-input"
                                               wire:click="selectDebt({{ $debt->id }})"
                                               value="{{ $debt->id }}">
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    @lang("translation.groups.no_debt")
                                </td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-12">
            <div class="mb-3 d-flex justify-content-center align-items-center fs-1" style="height: 100%">
                <i class="fa-solid fa-circle-right text-success"></i>
            </div>
        </div>
        <div class="col-md-5 col-sm-12">
            <div class="card h-100">
                <div class="p-2 card-header">
                    <div class="mx-auto mb-1 d-flex justify-content-center" wire:ignore>
                        <select class="form-select transfer-group-select" id="transfer_groups" name="transfer_group_id"
                                wire:model="selectedTransferGroupId">
                            <option value="" disabled selected>
                                <span>@lang("translation.groups.select_group")</span>
                            </option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}"
                                        @if($group->id == $selectedTransferGroupId) selected @endif>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    @if(!$selectedTransferGroup)
                        <div class="text-center">
                            <img src="{{ asset('assets/images/transfer-select.webp') }}"
                                 alt="select-patient" style="height: 300px">
                            <div class="mt-3">
                                <h5>
                                    @lang("translation.groups.select_group")!
                                </h5>
                            </div>
                        </div>
                    @else
                        <h5 class="mb-1">
                            <a href="{{ route('admin.groups.show', $selectedTransferGroup->id) }}" class="fw-medium">
                                {{ $selectedTransferGroup->name }}
                            </a>
                        </h5>
                        <p class="mb-0 text-muted">@lang("attr.create_date")
                            : {{ dateFormat($selectedTransferGroup->created_at) }}</p>
                        <ul class="my-3 list-group list-group-unbordered">
                            <li class="list-group-item d-flex justify-content-between">
                                <b>@lang("translation.groups.teacher")</b>
                                <a href="{{ route('admin.users.show', $selectedTransferGroup->teacher_id ?? 0) }}"
                                   class="fw-bold">
                                    {{ $selectedTransferGroup->teacher?->name ?? '-' }}
                                </a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <b> @lang("translation.groups.course")</b>
                                <a class="float-right">{{ $selectedTransferGroup->course->name ?? '-' }}</a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <b>@lang("translation.groups.branch")</b>
                                <a class="float-right">{{ $selectedTransferGroup->branch->name ?? '-' }}</a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <b>@lang("translation.groups.amount")</b>
                                <a class="float-right">
                                    {{ money($selectedTransferGroup->amount) }}
                                </a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <b>@lang("translation.groups.monthly_payment_type")</b>
                                <a class="float-right">
                                    {{ __('translation.groups.'.$selectedTransferGroup->payment_type) }}
                                </a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <b>@lang("translation.groups.start_date")</b>
                                <a class="float-right">
                                    {{ dateFormat($selectedTransferGroup->start_date) }}
                                </a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <b>@lang("translation.groups.debt_range")</b>
                                <a class="float-right">
                                    {{ $selectedTransferGroup->getPaymentDate() }}
                                </a>
                            </li>
                        </ul>
                        <hr>
                        <h5>
                            {{ __('translation.students.debts') }}
                        </h5>
                        <table class="table table-sm">
                            <tr>
                                <th>ID</th>
                                <th>@lang("translation.groups.debt_range")</th>
                                <th>@lang("attr.left_amount")</th>
                                <th>@lang("translation.groups.will_deleted")</th>
                            </tr>
                            @forelse($transferGroupDebts as $transferGroupDebt)
                                <tr>
                                    <td>{{ $transferGroupDebt->id }}</td>
                                    <td>{{ $transferGroupDebt->range }}</td>
                                    <td>{{ money($transferGroupDebt->left_amount) }}</td>
                                    <td>
                                        <div class="mb-3 form-check form-switch">
                                            <input type="checkbox" class="form-check-input"
                                                   wire:click="selectDebt({{ $transferGroupDebt->id }})"
                                                   value="{{ $transferGroupDebt->id }}">
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        @lang("translation.groups.no_debt")
                                    </td>
                                </tr>
                            @endforelse
                        </table>
                    @endif
                </div>
            </div>
        </div>
        <div class="mt-3 text-center">
            <button class="btn btn-primary" @if(!$selectedTransferGroup) disabled @endif wire:click="transfer">
                <i class="fas fa-save"></i> @lang("translation.students.transfer_group")
            </button>
        </div>
    </div>
    @push('custom-scripts')
        <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $('.group-select2').select2({});
                $('.group-select2').on('select2:select', function (e) {
                    let value = $(this).val();
                    Livewire.dispatch('changeStudentGroup', {group_id: value});
                });
                $('.transfer-group-select').select2({});
                $('.transfer-group-select').on('select2:select', function (e) {
                    let value = $(this).val();
                    Livewire.dispatch('changeTransferGroup', {group_id: value});
                });
            });
        </script>
    @endpush
</div>
