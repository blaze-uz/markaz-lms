<div class="container">
    @if($reminders->count())
        @foreach($reminders as $date => $remindersOnDate)
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">{{ dateFormat($date) }}</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($remindersOnDate as $reminder)
                            <li class="list-group-item">
                                <a href="{{ route('admin.leads.table', ['search' => $reminder->lead->name ?? '-']) }}"
                                   class="text-reset">
                                    <div class="reminder-item">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p>
                                                    <strong>
                                                        @lang("translation.leads.lead"):
                                                    </strong>
                                                    {{ $reminder->lead->name ?? '-' }}
                                                </p>
                                                <p>
                                                    <strong>
                                                        @lang("translation.leads.date"):
                                                    </strong>
                                                    {{ dateFormat($reminder->reminder_date ?? '-') }}
                                                </p>
                                                <p>
                                                    <strong>
                                                        @lang("translation.leads.time"):
                                                    </strong>
                                                    {{ dateFormat($reminder->reminder_time ?? '-', 'H:i') }}
                                                </p>
                                                <p>
                                                    <strong>
                                                        @lang("translation.leads.comment"):
                                                    </strong>
                                                    {{ $reminder->reminder_note ?? '-' }}
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p>
                                                    <strong>@lang("attr.status"):</strong>
                                                    <span class="badge bg-danger-subtle text-danger">
                                                        {{ __("translation.leads." . ($reminder->reminder_status ?? 'unknown')) }}
                                                    </span>
                                                </p>
                                                <p>
                                                    <strong>@lang("translation.students.created_by"):</strong>
                                                    {{ $reminder->causer->name ?? '-' }}
                                                </p>
                                                <p>
                                                    <strong>
                                                        @lang("attr.created_at"):
                                                    </strong>
                                                    {{ dateFormat($reminder->created_at ?? '-', 'd.m.Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-warning" role="alert">
            <x-no-result/>
        </div>
    @endif
</div>