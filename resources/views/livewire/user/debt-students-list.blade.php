<div>
    <div class="row g-0 text-center mb-3">
        <div class="col-6 col-sm-4">
            <div class="p-3 border border-dashed border-start-0">
                <h5 class="mb-1">
                    <div>
                        {{ money($debtCardData['allDebtSum']) }}
                    </div>
                </h5>
                <p class="text-muted mb-0">
                    @lang("translation.debt_amount")
                </p>
            </div>
        </div>
        <div class="col-6 col-sm-4">
            <div class="p-3 border border-dashed border-start-0">
                <h5 class="mb-1">
                    <div>
                        {{ money($debtCardData['leftDebtSum']) }}
                    </div>
                </h5>
                <p class="text-muted mb-0">
                    @lang("translation.left_debt_amount")
                </p>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="bg-primary-subtle">
            <tr>
                <th>ID</th>
                <th>@lang("translation.students.full_name")</th>
                <th>@lang("translation.students.payment_amount")</th>
                <th>@lang("attr.left_amount")</th>
                <th>@lang("translation.students.debt_range")</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($debtStudents as $studentGroupPayment)
                <tr custom-data-toggle-trigger="#payment_data_{{$studentGroupPayment->id}}" class="cursor-pointer">
                    <td>{{ $studentGroupPayment->id }}</td>
                    <td>
                        @if($studentGroupPayment->student && $studentGroupPayment->student->deleted_at)
                            <strike data-bs-toggle="tooltip" title="{{__('translation.students.deleted')}}">
                                {{ $studentGroupPayment->student->getFullName() }}
                            </strike>
                        @else
                            {{ $studentGroupPayment->student?->getFullName() ?? __('translation.students.deleted_permanently') }}
                        @endif
                    </td>
                    <td class="text-nowrap">{{ money($studentGroupPayment->amount) }}</td>
                    <td class="text-danger text-nowrap">{{ money($studentGroupPayment->left_amount) }}</td>
                    <td class="text-nowrap">{{ $studentGroupPayment->range }}</td>
                    <td>
                        @if($studentGroupPayment->group->state == 'active')
                            @can('group-update-debt')
                                <a href="{{ route('admin.groups.edit-debt', $studentGroupPayment->id) }}"
                                   data-bs-toggle="tooltip" title="{{__('translation.groups.edit-debt')}}">
                                    <i class="mdi mdi-pencil"></i>
                                </a>
                            @endcan
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        @lang("translation.no_data")
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
