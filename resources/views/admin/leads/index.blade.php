@extends('layouts.master')
@section('title')
@lang("translation.leads.leads")
@endsection
@section("content")
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="card">
    <div class="card-body d-flex justify-content-end gap-2">
        <a href="{{ route('admin.leads.table') }}" class="btn btn-primary">
            <i class="ri-table-line"></i>
            @lang("translation.leads.table")
        </a>
        <a href="{{ route('admin.leads.settings') }}" class="btn btn-info d-flex align-items-center gap-1">
            <i class="ri-settings-3-line"></i>
            @lang("translation.leads.settings")
        </a>
        <div class="dropdown">
            <button class="btn btn-success d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="ri-add-line"></i>
                @lang("translation.leads.create_lead")
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="{{ route('admin.leads.create') }}" class="dropdown-item d-flex align-items-center gap-1">
                        <i class="ri-add-line"></i>
                        @lang("translation.btn.add")
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.leads.import') }}" class="dropdown-item d-flex align-items-center gap-1">
                        <i class="ri-file-excel-2-line"></i>
                        @lang("translation.leads.import_from_excel")
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<livewire:leads.kanban-board />
@endsection
