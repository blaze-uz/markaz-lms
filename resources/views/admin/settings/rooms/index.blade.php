@extends('layouts.master')
@section('title')
    @lang('translation.rooms-settings')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    @lang("translation.btn.add")
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>@lang("translation.rooms.name")</th>
                    <th>@lang("translation.rooms.capacity")</th>
                    <th>@lang("translation.rooms.branch")</th>
                    <th>@lang("translation.rooms.status")</th>
                    <th></th>
                </tr>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->capacity ?? '-' }}</td>
                        <td>{{ $item->branch->name ?? '-' }}</td>
                        <td>
                            @if($item->status == 'available')
                                <span class="badge bg-success-subtle text-success">
                                    @lang("translation.rooms.available")
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">
                                    @lang("translation.rooms.unavailable")
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.rooms.edit', $item->id) }}"
                                   class="btn btn-success">
                                    <i class="fas fa-edit"></i>
                                    @lang("translation.btn.edit")
                                </a>
                                <form action="{{ route('admin.rooms.destroy', $item->id) }}"
                                      method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger"
                                            onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                        @lang("translation.btn.delete")
                                    </button>
                                </form>
                            </div>
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
            <div class="d-flex justify-content-end">
                {{ $items->links() }}
            </div>
        </div>
    </div>
@endsection
