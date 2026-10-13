@extends('layouts.master')
@section('title')
@lang('translation.settings')
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="card">
    <div class="card-header">
        <div class="gap-2 d-flex justify-content-end">
            <a href="{{ route('admin.settings.payment-types.reorder') }}" class="btn btn-success">
                <i class="fas fa-sort"></i>
                @lang("translation.reorder")
            </a>
            <a href="{{ route('admin.settings.payment-types.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                @lang("translation.btn.add")
            </a>
        </div>
    </div>
    <div class="card-body">
        <table class="table">
            <tr>
                <th>ID</th>
                <th>@lang("translation.payments.type-name")</th>
                <th></th>
            </tr>
            @forelse($paymentTypes as $type)
            <tr>
                <td>{{ $type->id }}</td>
                <td>{{ $type->name }}</td>
                <td>
                    <div class="gap-2 d-flex">
                        <a href="{{ route('admin.settings.payment-types.edit', $type->id) }}" class="btn btn-success">
                            <i class="fas fa-edit"></i>
                            @lang("translation.btn.edit")
                        </a>
                        <form action="{{ route('admin.settings.payment-types.delete', $type->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                                @lang("translation.btn.delete")
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="100">
                    <x-no-result />
                </td>
            </tr>
            @endforelse
        </table>
    </div>
</div>
@endsection