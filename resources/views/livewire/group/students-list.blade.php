<div>
    <div class="gap-2 mb-3 d-flex justify-content-between">
        <div class="d-flex gap-2">
            <x-search parent-class="m-0" />
            <select class="form-select" style="width: 150px" wire:model.live="filter">
                <option value="all">@lang('translation.all')</option>
                <option value="debt">@lang('translation.students.debt')</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            @if (count($selectedStudents) > 0)
                <button class="btn btn-warning" wire:click="sendSms">
                    <i class="ri-mail-line me-1"></i>
                    @lang('translation.sms.send')
                </button>
                @canany(['group-detach-student', 'student-detach-group'])
                    <button class="btn btn-danger" wire:click="bulkDetach">
                        <i class="ri-user-unfollow-line me-1"></i>
                        @lang('translation.groups.detach_selected') ({{ count($selectedStudents) }})
                    </button>
                @endcanany
            @endif
            @can('group-attach-student')
                <a href="{{ route('admin.groups.attach-student.view', ['group_id' => $group->id]) }}"
                    class="btn btn-primary d-flex align-items-center">
                    <i class="ri-user-add-line me-1"></i>
                    @lang('translation.add')
                </a>
            @endcan
        </div>
    </div>
    <div class="table-responsive">
        @if ($students->count())
            <table class="table mb-0 align-middle table-nowrap">
                <thead>
                    <tr>
                        <th class="py-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault"
                                    wire:model.live="selectAll" wire:change="selectAll"
                                    @if (count($selectedStudents) === count($students)) checked @endif />
                            </div>
                        </th>
                        <th class="py-2">ID</th>
                        <th class="py-2">@lang('translation.students.name')</th>
                        @can('student-show')
                            <th class="py-2">@lang('translation.students.phone')</th>
                        @endcan
                        <th class="py-2">@lang('attr.balance')</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $student)
                        @php
                            $balance = $student->balance;
                        @endphp
                        <tr>
                            <td class="py-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="student_{{ $student->id }}"
                                        wire:model.live="selectedStudents" value="{{ $student->id }}" />
                                </div>
                            </td>
                            <td class="py-2">{{ $student->id }}</td>
                            <td class="py-2 text-nowrap text-truncate" style="max-width: 270px">
                                @can('student-show')
                                    <a href="{{ route('admin.students.show', $student->id) }}"
                                        class="text-reset text-decoration-none">
                                        <span class="me-2">
                                            {!! Avatar::create($student->getFullName())->setTheme('grayscale-dark')->setDimension(32)->setFontSize(12)->toSvg() !!}
                                        </span>
                                        {{ $student->getFullName() }}
                                    </a>
                                @else
                                    <div>
                                        <span class="me-2">
                                            {!! Avatar::create($student->getFullName())->setTheme('grayscale-dark')->setDimension(32)->setFontSize(12)->toSvg() !!}
                                        </span>
                                        {{ $student->getFullName() }}
                                    </div>
                                @endcan
                            </td>
                            @can('student-show')
                                <td class="py-2">{{ $student->getFormattedPhone() }}</td>
                            @endcan
                            <td class="py-2">
                                <span
                                    class="badge bg-{{ $balance < 0 ? 'danger' : 'success' }}-subtle text-{{ $balance < 0 ? 'danger' : 'success' }}">
                                    {{ money($balance) }}
                                </span>
                            </td>
                            <td class="py-2 text-end">
                                @if ($group->state !== 'completed')
                                    <div class="gap-2 d-flex align-items-center justify-content-end">
                                        @if ($student->groups->where('id', $group->id)->first()?->pivot?->amount == null)
                                            @can('group-add-student-agreement')
                                                <a href="{{ route('admin.groups.agreement-view', [
                                                    'group' => $group->id,
                                                    'student' => $student->id,
                                                ]) }}"
                                                    class="border t-agreement-btn btn btn-sm btn-primary waves-effect waves-light material-shadow-none">
                                                    <i class="mdi mdi-file-document"></i>
                                                </a>
                                            @endcan
                                        @endif
                                        @can('student-payment')
                                            <a href="{{ route('admin.students.payment.view', $student->id) }}"
                                                class="border t-payment-btn btn btn-sm btn-primary waves-effect waves-light material-shadow-none"
                                                title="{{ __('translation.students.payment') }}">
                                                <i class="mdi mdi-cash-multiple"></i>
                                            </a>
                                        @endcan
                                        @canany(['group-detach-student', 'student-detach-group'])
                                            <form action="{{ route('admin.groups.detach') }}" method="POST"
                                                class="d-inline-block">
                                                @csrf
                                                <input type="hidden" name="group_id" value="{{ $group->id }}">
                                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                                <button type="submit" class="btn btn-sm t-detach-btn btn-danger"
                                                    onclick="return confirm('Are you sure?')">
                                                    <i class="mdi mdi-account-remove"></i>
                                                </button>
                                            </form>
                                        @endcanany
                                        @if ($student->groups->where('id', $group->id)->first()?->pivot?->amount !== null)
                                            <a class="border t-info-btn btn btn-sm btn-primary waves-effect waves-light material-shadow-none"
                                                wire:click="showInfo({{ $student->id }})">
                                                <i class="mdi mdi-information-outline"></i>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="mt-3">
                <x-no-result />
            </div>
        @endif
        <div class="my-3">
            {{ $students->links() }}
        </div>
    </div>
    <div class="card-preloader d-none" wire:loading.class.remove="d-none">
        <div class="card-status">
            <div class="spinner-border text-success">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Info Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">
                        {{ __('translation.students.info') }}
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($selectedStudent)
                        <table class="table">
                            <tr>
                                <th>{{ __('translation.students.name') }}</th>
                                <td>{{ $selectedStudent->getFullName() }}</td>
                            </tr>
                            @can('student-show')
                                <tr>
                                    <th>{{ __('translation.students.phone') }}</th>
                                    <td>{{ $selectedStudent->getFormattedPhone() }}</td>
                                </tr>
                            @endcan
                            <tr>
                                <th>{{ __('translation.students.attached_date') }}</th>
                                <td>
                                    {{ dateFormat($selectedStudent->attached_at) }}
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('translation.groups.group_agreed_amount') }}</th>
                                <td>
                                    {{ money($selectedStudent->amount) }}
                                </td>
                            </tr>
                            @if ($selectedStudent->comment)
                                <tr>
                                    <th>{{ __('translation.groups.comment') }}</th>
                                    <td>{{ $selectedStudent->comment }}</td>
                                </tr>
                            @endif
                        </table>
                    @endif
                </div>
                <div class="modal-footer">
                    {{-- delete action --}}
                    <form action="{{ route('admin.groups.delete-agreement', $group->id ?? 0) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="student_id" value="{{ $selectedStudent->id ?? 0 }}">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                            {{ __('translation.delete_agreement') }}
                        </button>
                    </form>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        {{ __('translation.Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Detach Modal -->
    <div class="modal fade" id="bulkDetachModal" tabindex="-1" aria-labelledby="bulkDetachModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkDetachModalLabel">
                        <i class="ri-user-unfollow-line me-2"></i>
                        @lang('translation.groups.bulk_detach_students')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="ri-alert-line me-2"></i>
                        @lang('translation.groups.bulk_detach_warning')
                    </div>

                    @if (!empty($studentsToDetach))
                        <div class="mb-3">
                            <h6>@lang('translation.students.students_with_debts'):</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>@lang('translation.students.name')</th>
                                            <th>@lang('translation.students.debt_amount')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($studentsToDetach as $student)
                                            @if (isset($student['group_payments']) && count($student['group_payments']) > 0)
                                                <tr>
                                                    <td>{{ $student['name'] }} {{ $student['surname'] }}</td>
                                                    <td class="text-danger fw-bold">
                                                        {{ money(collect($student['group_payments'])->sum('left_amount')) }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info">
                                <i class="ri-information-line me-2"></i>
                                @lang('translation.groups.debt_will_be_deleted_warning')
                            </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        @lang('translation.btn.cancel')
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="processBulkDetach"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="processBulkDetach">
                            <i class="ri-user-unfollow-line me-1"></i>
                            @lang('translation.groups.detach_students')
                        </span>
                        <span wire:loading wire:target="processBulkDetach">
                            <i class="ri-loader-4-line me-1 spinner-border spinner-border-sm"></i>
                            @lang('translation.processing')...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('custom-scripts')
        <script>
            tippy('.t-payment-btn', {
                content: "{!! __('translation.students.payment') !!}",
            });

            tippy('.t-detach-btn', {
                content: "{!! __('translation.groups.detach') !!}",
            });

            tippy('.t-debt-badge', {
                content: "{!! __('translation.students.loan') !!}",
            });

            tippy('.t-info-btn', {
                content: "{!! __('translation.students.info') !!}",
            });

            tippy('.t-agreement-btn', {
                content: "{!! __('translation.students.add_agreement') !!}",
            });
        </script>
        @script
            <script>
                $wire.on('showInfo', (data) => {
                    $('#exampleModal').modal('show');
                });

                $wire.on('show-bulk-detach-modal', () => {
                    $('#bulkDetachModal').modal('show');
                });

                $wire.on('hide-bulk-detach-modal', () => {
                    $('#bulkDetachModal').modal('hide');
                });

                // Reset modal data when hidden
                $('#bulkDetachModal').on('hidden.bs.modal', function() {
                    @this.set('studentsToDetach', []);
                });
            </script>
        @endscript
    @endpush
</div>
