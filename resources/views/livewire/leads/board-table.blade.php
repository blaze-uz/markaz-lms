<div>
    <div class="d-flex justify-content-between align-items-baseline flex-wrap mb-3">
        <x-search/>
        @lang('translation.leads.boards')
        <a href="{{ route('admin.leads.settings.create-board') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i>
            @lang('translation.leads.add_board')
        </a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>@lang('translation.leads.board_name')</th>
                        <th>@lang('translation.leads.attached_group')</th>
                        <th></th>
                    </tr>
                    @forelse($items as $item)
                        <tr>
                            <td>{{$item->id}}</td>
                            <td>{{$item->name}}</td>
                            <td>

                                <a href="{{ route('admin.groups.show', $item->group_id ?? 0) }}">
                                    <span class="badge bg-success-subtle text-success">
                                        {{$item->group?->name ?? '-'}}
                                    </span>
                                </a>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.boards.show', $item->id) }}"
                                   class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.leads.settings.edit-board', $item->id) }}"
                                   class="btn btn-sm btn-success"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.leads.settings.delete-board', $item->id) }}"
                                      method="POST"
                                      class="d-inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100" class="text-center">
                                <x-no-result/>
                            </td>
                        </tr>
                    @endforelse
                </table>
                {{ $items->links() }}
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
</div>
