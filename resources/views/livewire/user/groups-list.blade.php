<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-baseline">
        <h5 class="mb-3 card-title">
            @lang('translation.groups.groups')
            <span class="badge bg-primary-subtle text-primary">{{ $groups_count }}</span>
        </h5>
        <div class="gap-3 mb-3 d-flex align-items-center">
            <div>
                <select name="state" id="group-state" class="form-select form-control" wire:model.live="state">
                    <option value="all" selected>@lang("translation.all")</option>
                    <option value="pending">@lang("translation.groups.demo")</option>
                    <option value="active">@lang("translation.groups.active")</option>
                    <option value="completed">@lang("translation.groups.completed")</option>
                </select>
            </div>
            <x-search parent-class="m-0" />
            <a href="{{ route('admin.groups.create', ['teacher_id' => $user->id]) }}"
                class="btn btn-sm btn-success t-create-btn">
                <span class="mdi mdi-plus"></span>
            </a>
        </div>
    </div>
    <table class="table table-hover">
        <thead class="bg-primary-subtle">
            <tr>
                <th>ID</th>
                <th>@lang("translation.groups.name")</th>
                <th>@lang("translation.groups.branch")</th>
                <th>@lang("translation.groups.students_count")</th>
                <th>@lang("translation.courses.price")</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups as $group)
            <tr>
                <td>{{ $group->id }}</td>
                <td class="text-nowrap">
                    {{ $group->name }}
                    @if($group->state == 'completed')
                    <span class="badge bg-warning-subtle text-warning">
                        <i class="fa-solid fa-hourglass-half" data-bs-toggle="tooltip"
                            title="{{__('translation.groups.completed')}}"></i>
                    </span>
                    @endif
                </td>
                <td>
                    @foreach($group->branches as $branch)
                    <span class="badge bg-info-subtle text-info">
                        {{ $branch->name }}
                    </span>
                    @endforeach
                </td>
                <td>
                    <span class="badge bg-success-subtle text-success">
                        {{ $group->students_count }}
                    </span>
                </td>
                <td class="text-nowrap">{{ money($group->price) }}</td>
                <td>
                    <a href="{{ route('admin.groups.show',['group' => $group->id]) }}"
                        class="btn bg-primary-subtle text-primary btn-sm t-show-btn">
                        <span class="mdi mdi-eye"></span>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="100" class="text-center">
                    <x-no-result />
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="card-preloader d-none" wire:loading.class.remove="d-none">
        <div class="card-status">
            <div class="spinner-border text-success">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>
