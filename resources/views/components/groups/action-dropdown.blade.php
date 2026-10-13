@props(['item'])

@if ($item->state !== 'completed')
    <div class="dropdown card-header-dropdown">
        <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
            <span class="border fs-5 btn btn-sm btn-ghost-dark waves-effect waves-light material-shadow-none">
                <i class="align-middle mdi mdi-dots-vertical"></i>
            </span>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item d-flex align-items-center gap-1"
                href="{{ route('admin.groups.edit', ['group' => $item->id]) }}">
                <i class="ri-settings-line text-primary"></i>
                @lang('translation.settings')
            </a>

            @if ($item->state == 'active' || $item->state == 'demo')
                <a class="dropdown-item d-flex align-items-center gap-1"
                    href="{{ route('admin.groups.debt-settings', $item->id) }}">
                    <i class="ri-money-dollar-circle-line text-primary"></i>
                    @lang('translation.groups.debt_settings')
                </a>
            @endif

            @can('group-attach-student')
                <form action="{{ route('admin.groups.attach-student.view') }}">
                    <input type="hidden" name="group_id" value="{{ $item->id }}">
                    <button type="submit" class="btn text-start dropdown-item d-flex align-items-center gap-1">
                        <i class="ri-user-add-fill text-primary"></i>
                        @lang('translation.groups.attach_student')
                    </button>
                </form>
            @endcan

            @if ($item->state == 'active')
                <x-groups.active-group-actions :item="$item" />
            @endif

            @if ($item->state == 'demo')
                <x-groups.demo-group-actions :item="$item" />
            @endif

            @can('group-delete')
                <form action="{{ route('admin.groups.destroy', $item->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item" onclick="return confirm('@lang('translation.groups.delete_confirm')')">
                        <span class="mdi mdi-delete text-danger"></span>
                        @lang('translation.btn.delete')
                    </button>
                </form>
            @endcan
        </div>
    </div>
@endif
