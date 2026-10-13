<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="card-title">
                @lang('translation.telegram.ids')
            </div>
            <div class="d-flex gap-2">
                <input class="form-control"
                       type="search"
                       placeholder="{{__('translation.search')}}"
                       aria-label="{{__('translation.search')}}"
                       wire:model.live.debounce.500ms="search">
                <a href="{{ route('admin.telegrams.create') }}" class="btn btn-primary">
                    @lang('attr.add')
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($telegrams->count())
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>@lang('translation.telegram.name')</th>
                        <th>@lang('translation.telegram.id')</th>
                        <th></th>
                    </tr>
                    @foreach($telegrams as $telegram)
                        <tr>
                            <td>{{ $telegram->id }}</td>
                            <td class="text-nowrap">{{ $telegram->name }}</td>
                            <td class="text-nowrap">
                                <span class="badge bg-success-subtle text-success">
                                    {{ $telegram->telegram_id }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <form action="{{ route('admin.telegrams.destroy', $telegram->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn bg-danger-subtle text-danger btn-sm"
                                            onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @else
            <x-no-result/>
        @endif
        {{ $telegrams->links() }}
    </div>
    <div class="card-preloader d-none" wire:loading.class.remove="d-none">
        <div class="card-status">
            <div class="spinner-border text-success">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>