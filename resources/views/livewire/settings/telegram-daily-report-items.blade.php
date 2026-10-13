<div class="mt-3">
    @if($existingTelegrams->count() > 0)
        <div class="card">
            <div class="card-header">
                @lang("translation.telegram.existing_telegram_ids")
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>@lang("translation.telegram.name")</th>
                            <th>@lang("translation.actions")</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($existingTelegrams as $existingTelegram)
                            <tr>
                                <td>{{ $existingTelegram->id }}</td>
                                <td>{{ $existingTelegram->name }}
                                    <span class="badge bg-success-subtle text-success">
                                        {{$existingTelegram->telegram_id}}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('admin.telegrams.delete-daily-report-item', $existingTelegram->id) }}"
                                          method="POST">
                                        @csrf
                                        <button type="submit" class="btn bg-danger-subtle text-danger btn-sm"
                                                data-bs-toggle="tooltip" title="{{__('translation.btn.delete')}}"
                                                onclick="return confirm('Are you sure you want to delete this item?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
