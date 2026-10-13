@extends('layouts.master')
@section('title')
@lang("translation.leads.board")
@endsection
@section('css')
<style>
    .card {
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 0 25px rgba(0,0,0,0.1);
    }
    .card-header {
        background: none;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1.5rem;
    }
    .card-body {
        padding: 1.5rem;
    }
    .table th {
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        border-top: none;
    }
    .table td {
        padding: 1rem;
        color: #6c757d;
    }
    .btn {
        padding: 0.5rem 1rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .btn i {
        margin-right: 4px;
    }
    .btn:hover {
        transform: translateY(-1px);
    }
    .btn-primary {
        box-shadow: 0 2px 6px rgba(63,81,137,0.15);
    }
    .btn-warning {
        box-shadow: 0 2px 6px rgba(255,193,7,0.15);
    }
    .btn-success {
        box-shadow: 0 2px 6px rgba(40,167,69,0.15);
    }
    .shareLink {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-top: 1rem;
    }
    .permalink {
        position: relative;
        margin-top: 0.5rem;
    }
    .textLink {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        font-size: 0.9rem;
        color: #495057;
        background: white;
        transition: all 0.2s;
    }
    .textLink:focus {
        border-color: #3f5189;
        box-shadow: 0 0 0 0.2rem rgba(63,81,137,0.15);
    }
    .copyLink {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .copyLink:hover {
        background: rgba(63,81,137,0.1);
    }
    .copyLink i {
        color: #3f5189;
        font-size: 1.1rem;
    }
    .leads-count {
        background: #e8eaf3;
        color: #3f5189;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.9rem;
        margin-left: 0.75rem;
    }
    .alert {
        border: none;
        border-radius: 8px;
        padding: 1rem 1.5rem;
    }
</style>
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title mb-0">@lang("translation.leads.board")</h4>
                        <span class="leads-count">{{ $item->tasks->count() ?? 0 }} @lang("translation.leads.leads")</span>
                    </div>
                    <a href="{{ route('admin.leads.create', ['board' => $item->id]) }}"
                        class="btn btn-primary d-flex align-items-center">
                        <i class="ri-add-line"></i>
                        @lang("translation.leads.add_lead")
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <tr>
                        <th>{{ __("translation.leads.board_name") }}</th>
                        <td>{{ $item->name }}</td>
                    </tr>
                    @if($item->group_id)
                    <tr>
                        <th>{{ __("translation.leads.attached_group") }}</th>
                        <td>
                            <a href="{{ route('admin.groups.show', $item->group?->id ?? 0) }}" class="text-primary">
                                {{ $item->group->name ?? '-' }}
                            </a>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <th>{{ __("translation.leads.form_course_state") }}</th>
                        <td>
                            <livewire:leads.kanban-board-form-course-switcher :item="$item" />
                        </td>
                    </tr>
                </table>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="{{ route('admin.leads.settings.edit-board', $item->id) }}"
                        class="btn btn-warning d-flex align-items-center">
                        <i class="ri-pencil-line"></i>
                        @lang("translation.leads.edit_board")
                    </a>
                    @if($item->group_id)
                    <a href="{{ route('admin.boards.transfer-to-group-view', $item->id) }}"
                        class="btn btn-success d-flex align-items-center">
                        <i class="ri-exchange-line"></i>
                        @lang("translation.leads.transfer_leads")
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @if($item->url)
        <div class="shareLink">
            <label class="fw-medium mb-2" for="text">
                {{ __("translation.leads.board_url") }}
            </label>
            <div class="permalink">
                <input class="textLink" id="text" type="text" name="shortlink"
                    value="{{ app('url')->to('/').'/lead/'.$item->url }}" readonly>
                <span class="copyLink" id="copy" data-bs-toggle="tooltip" data-bs-placement="top" title="Copy to clipboard">
                    <i class="ri-file-copy-line"></i>
                </span>
            </div>
        </div>
        @else
        <div class="alert alert-warning d-flex justify-content-between align-items-center mt-3">
            <div>
                @lang("translation.leads.board_url_not_set")
            </div>
            <form action="{{ route('admin.boards.set-url', $item->id) }}" method="POST">
                @csrf
                <button class="btn btn-warning d-flex align-items-center" type="submit">
                    <i class="ri-settings-4-line"></i>
                    @lang("translation.leads.set_url")
                </button>
            </form>
        </div>
        @endif
    </div>
    <div class="col-md-8">
        <livewire:leads.board-leads-table :board="$item" />
    </div>
</div>
@endsection
@section('script-bottom')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById("text");
        const copyButton = document.getElementById("copy");
        const tooltip = new bootstrap.Tooltip(copyButton);

        copyButton.addEventListener("click", () => {
            input.select();
            document.execCommand("copy");

            // Update tooltip
            tooltip.dispose();
            copyButton.setAttribute('title', 'Copied!');
            copyButton.setAttribute('data-bs-original-title', 'Copied!');
            tooltip = new bootstrap.Tooltip(copyButton);
            tooltip.show();

            // Reset tooltip after 2 seconds
            setTimeout(() => {
                tooltip.dispose();
                copyButton.setAttribute('title', 'Copy to clipboard');
                copyButton.setAttribute('data-bs-original-title', 'Copy to clipboard');
                tooltip = new bootstrap.Tooltip(copyButton);
            }, 2000);
        });
    });
</script>
@endsection
