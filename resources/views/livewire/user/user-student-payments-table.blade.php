<div>
    <h5 class="mb-3">
        {{ __("translation.groups.groups") }}
    </h5>
    <div class="card rounded-4">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                <select class="form-select form-select-sm w-25" wire:model.live="selectedDate">
                    <option value="this_month">
                        {{ __("translation.users.this_month") }}
                    </option>
                    <option value="last_month">
                        {{ __("translation.users.last_month") }}
                    </option>
                    <option value="all_time">
                        {{ __("translation.users.all_time") }}
                    </option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-primary-subtle">
                    <tr>
                        <th>ID</th>
                        <th>{{ __('translation.groups.group') }}</th>
                        <th>{{ __('translation.students.count') }}</th>
                        <th>{{ __('translation.students.payments') }}</th>
                        <th class="text-nowrap">{{ __('translation.students.payment_status') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($groups as $group)
                        <tr>
                            <td>{{ $group->id }}</td>
                            <td>
                                <a href="{{ route('admin.groups.show', $group->id) }}">
                                    {{ $group->name }}
                                </a>
                            </td>
                            <td>{{ $group->students_count }}</td>
                            <td class="text-nowrap">
                                <div class="d-flex flex-column">
                                    <span class="text-muted">
                                        {{__('translation.users.all_debt_amount')}} :
                                        <span class="fw-medium text-success">
                                            {{money($group->all_debt_sum)}}
                                        </span>
                                    </span>
                                    <span class="text-muted">
                                        {{__('translation.users.left_amount')}} :
                                        <span class="fw-medium text-danger">
                                            {{money($group->left_debt_sum)}}
                                        </span>
                                    </span>
                                </div>
                            </td>
                            <td>
                                @php
                                    $percentage = 0;

                                    if($group->all_debt_sum > 0){
                                        $percentage = round((($group->all_debt_sum - $group->left_debt_sum) / $group->all_debt_sum) * 100);
                                    }
                                @endphp
                                <div class="progress animated-progress custom-progress progress-label">
                                    <div class="progress-bar bg-danger" role="progressbar"
                                         style="width: {{$percentage}}%"
                                         aria-valuenow="{{$percentage}}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="label">{{$percentage}}%</div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
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
