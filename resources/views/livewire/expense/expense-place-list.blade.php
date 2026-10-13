<div>
    <div class="d-flex justify-content-between align-items-baseline flex-wrap mb-3">
        <x-search/>
        @lang('translation.expenses.places')
        <a href="{{ route('admin.expenses.settings.create-place') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i>
            @lang('translation.expenses.add_place')
        </a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>@lang('translation.expenses.place_name')</th>
                        <th></th>
                    </tr>
                    @forelse($items as $item)
                        <tr>
                            <td>{{$item->id}}</td>
                            <td>{{$item->name}}</td>
                            <td>
                                <a href="{{ route('admin.expenses.settings.edit-place', $item->id) }}"
                                   class="btn btn-sm btn-success"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.expenses.settings.delete-place', $item->id) }}"
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
