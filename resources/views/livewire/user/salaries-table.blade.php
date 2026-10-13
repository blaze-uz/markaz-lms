<div>
    @push('css')
        <style>
            .select2-container--default .select2-selection--single {
                width: 200px !important;
            }
        </style>
    @endpush
    <div class="row h-100">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body cursor-pointer">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-light text-success rounded-circle fs-3">
                                    <i class="ri-money-dollar-circle-fill align-middle"></i>
                                </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">
                                {{__('translation.users.calculated_amount')}}
                            </p>
                            <h4 class="mb-0 text-success">{{ money($items->sum('amount')) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body cursor-pointer">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-light text-success rounded-circle fs-3">
                                    <i class="ri-money-dollar-circle-fill align-middle"></i>
                                </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">
                                {{__('translation.users.given_amount')}}
                            </p>
                            <h4 class="mb-0 text-success">{{ money($items->sum('given_amount')) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h3 class="card-title">{{__('translation.users.salaries')}}</h3>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-primary t-export-btn" wire:click="exportExcel">
                        <i class="ri-file-excel-2-line fs-5"></i>
                    </button>
                    <div wire:ignore class="d-flex justify-content-between gap-2">
                        <select name="month" id="month" class="form-control month">
                            <option value="">{{__('translation.students.choose_month')}}</option>
                            <option value="1">{{__('translation.months.january')}}</option>
                            <option value="2">{{__('translation.months.february')}}</option>
                            <option value="3">{{__('translation.months.march')}}</option>
                            <option value="4">{{__('translation.months.april')}}</option>
                            <option value="5">{{__('translation.months.may')}}</option>
                            <option value="6">{{__('translation.months.june')}}</option>
                            <option value="7">{{__('translation.months.july')}}</option>
                            <option value="8">{{__('translation.months.august')}}</option>
                            <option value="9">{{__('translation.months.september')}}</option>
                            <option value="10">{{__('translation.months.october')}}</option>
                            <option value="11">{{__('translation.months.november')}}</option>
                            <option value="12">{{__('translation.months.december')}}</option>
                        </select>
                        <select name="staff" id="staff" class="form-control select2">
                            <option value="">{{__('translation.users.all_staff')}}</option>
                            @foreach($staff as $stf)
                                <option value="{{ $stf->id }}">{{ $stf->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>{{__('translation.users.name')}}</th>
                        <th>{{__('translation.users.amount')}}</th>
                        <th>{{__('translation.users.given_amount')}}</th>
                        <th>{{__('translation.users.which_month')}}</th>
                        <th>{{__('translation.users.date')}}</th>
                        <th>{{__('translation.actions')}}</th>
                    </tr>
                    @forelse($items as $salary)
                        <tr>
                            <td>{{ $salary->id }}</td>
                            <td>{{ $salary->staff?->name ?? '-' }}</td>
                            <td>{{ money($salary->amount) }}</td>
                            <td>{{ money($salary->given_amount) }}</td>
                            <td>{{ dateFormat($salary->date, 'F') }}</td>
                            <td>{{ dateFormat($salary->created_at) }}</td>
                            <td>
                                <a href="{{ route('admin.users.salaries.show', $salary->id) }}">
                                        <span data-bs-toggle="tooltip" title="{{__('translation.btn.show')}}">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100" class="text-center">{{__('translation.no_data')}}</td>
                        </tr>
                    @endforelse
                </table>
                {{ $items->links() }}
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
    @push('custom-scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function () {
                $('.select2').select2({
                    width: 'resolve'
                });
                $('.month').select2({
                    width: 'resolve'
                });
                tippy('.t-export-btn', {
                    content: '{{__('translation.students.export')}}',
                });
            });
        </script>
        @script
        <script>
            $('.select2').on('change', function (e) {
                $wire.dispatch('select2-changed', ['staff', e.target.value]);
            });
            $('.month').on('change', function (e) {
                $wire.dispatch('select2-changed', ['month', e.target.value]);
            });
        </script>
        @endscript
    @endpush
</div>
