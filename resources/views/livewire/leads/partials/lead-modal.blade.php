<!-- Lead Modal -->
<div wire:ignore.self class="modal fade" id="leadModal" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">@lang("translation.leads.lead")</h1>
                <div class="gap-1 d-flex justify-content-between align-items-center">
                    <!-- Dropdown Menu Item Color -->
                    <div class="btn-group">
                        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis text-muted"></i>
                        </button>
                        <div class="dropdown-menu dropdownmenu-primary">
                            <a class="dropdown-item" href="{{ route('admin.leads.edit', $lead?->id ?? 0) }}">
                                @lang("attr.edit")
                            </a>
                            <button class="dropdown-item" wire:click="removeTask({{$lead?->id}})">
                                @lang("translation.btn.delete")
                            </button>
                        </div>
                    </div><!-- btn-group -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body lead-form">
                <div class="mb-3 row">
                    <div class="col-md-7 col-sm-12">
                        <div class="mb-3 form-group input-group-lg">
                            <input type="text" class="p-0 border-0 form-control" value="{{$lead?->name}}"
                                wire:model="taskModalName" wire:change.stop="changeTaskName({{$lead}})">
                        </div>
                        <div>
                            <small class="text-muted">
                                @lang("translation.leads.phone")
                            </small>
                            <p>
                                {{ $lead?->phone }}
                            </p>
                        </div>
                        @if ($lead?->course_id)
                        <div>
                            <small class="text-muted">
                                @lang("translation.courses.course")
                            </small>
                            <p>
                                {{ $lead?->course->name ?? '-' }}
                            </p>
                        </div>
                        @endif
                        <div class="mb-3">
                            @if($lead?->chosen_days || $lead?->chosen_time)
                            <small class="text-muted">@lang("translation.leads.chosen_time")</small><br>
                            @endif
                            @if($lead?->chosen_days)
                            <span class="badge bg-success-subtle text-success">
                                {{ __('translation.leads.'.$lead?->chosen_days) }}
                            </span>
                            @endif
                            @if($lead?->chosen_time)
                            <span class="badge bg-success-subtle text-success">
                                {{dateFormat($lead?->chosen_time, 'H:i')}}
                            </span>
                            @endif
                        </div>
                        @if($lead?->teachers->count())
                        <small class="text-muted">
                            @lang("translation.leads.teachers")
                        </small>
                        <p>
                            @foreach($lead?->teachers as $teacher)
                            {{ $teacher->name }}
                            @endforeach
                        </p>
                        @endif
                        @if($lead?->comment)
                        <small class="text-muted">
                            @lang("translation.leads.comment")
                        </small>
                        <p>
                            {{ $lead?->comment }}
                        </p>
                        @endif
                        <div class="mt-5">
                            @if($activities)
                            <h6>@lang("translation.leads.activity")
                                <small class="text-muted">@lang("translation.leads.last_10_records")</small>
                            </h6>
                            <div class="gap-3 d-flex flex-column">
                                @foreach($activities as $activity)
                                <div class="d-flex flex-column">
                                    <div>
                                        @if($activity->description == 'created')
                                        <span class="fs-5">{{ $activity->causer->name ?? 'Unknown' }}</span>
                                        @lang("translation.leads.created_by") <span class="text-muted">{{
                                            $activity->created_at->diffForHumans() }}</span>
                                        @else
                                        <span class="fs-5">{{ $activity->causer->name ?? 'Unknown' }}</span>
                                        @lang("translation.leads.updated_by")
                                        @if ($activity->description == 'updated')
                                        &nbsp;
                                        <span class="text-muted">
                                            {{ $activity->created_at->diffForHumans() }}
                                        </span>
                                        <br>
                                        @php
                                        $changes = json_decode($activity->properties, true);
                                        $oldValues = $changes['old'] ?? [];
                                        $newValues = $changes['attributes'] ?? [];

                                        foreach ($newValues as $key => $newValue) {
                                        $oldValue = $oldValues[$key] ?? null;
                                        if($key == 'kanban_collection_id'){
                                        $oldValue = \App\Models\KanbanCollection::find($oldValue)->name ?? 'None';
                                        $newValue = \App\Models\KanbanCollection::find($newValue)->name ?? 'None';
                                        }
                                        if ($key == 'kanban_board_id') {
                                        $oldValue = \App\Models\KanbanBoard::find($oldValue)->name ?? 'None';
                                        $newValue = \App\Models\KanbanBoard::find($newValue)->name ?? 'None';
                                        }
                                        if ($key == 'attend') {
                                        $oldValue = $oldValue ? __('translation.leads.attended') :
                                        __('translation.leads.not_attended');
                                        $newValue = $newValue ? __('translation.leads.attended') :
                                        __('translation.leads.not_attended');
                                        }
                                        if ($oldValue !== $newValue) {
                                        echo "$oldValue &nbsp <span class='badge bg-success-subtle text-success'><i
                                                class='fas fa-arrow-right'></i></span> &nbsp $newValue<br>";
                                        }
                                        }
                                        @endphp
                                        @endif
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                            <div class="mb-3">
                                <form action="{{ route('admin.leads.add-comment') }}" class="gap-2 w-100 d-flex"
                                    method="POST">
                                    @csrf
                                    <div class="w-100">
                                        <input type="hidden" name="task_id" value="{{ $lead?->id }}">
                                        <input type="text" placeholder="{{__('translation.leads.comment')}}"
                                            name="comment" class="form-control @error('comment') is-invalid @enderror">
                                    </div>
                                    <button class="btn btn-success" type="submit">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                                @error('comment')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <h6>@lang("translation.leads.comments")</h6>
                                @if($comments)
                                @forelse($comments as $comment)
                                <div class="mb-3 d-flex justify-content-between flex-column">
                                    <div>
                                        <span class="fs-6">{{$comment->user->name ?? 'Unknown'}}</span>
                                        <span class="text-muted">{{$comment->created_at->diffForHumans()}}</span>
                                    </div>
                                    <div>
                                        💬 <span>{{$comment->comment}}</span>
                                    </div>
                                </div>
                                @empty
                                <div class="text-muted">@lang("translation.leads.no_comments")</div>
                                @endforelse
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-sm-12">
                        <div class="mb-3 d-flex justify-content-end flex-column">
                            <button class="btn btn-success leadStudentShowButton">
                                <i class="fas fa-plus"></i> @lang("translation.leads.add_as_student")
                            </button>
                            <div class="form-group d-none" id="leadStudent">
                                <ul class="mb-3 nav nav-tabs nav-border-top nav-border-top-success" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active text-success" data-bs-toggle="tab"
                                            href="#nav-border-add" role="tab" aria-selected="true">
                                            @lang("translation.leads.add")
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link text-success" data-bs-toggle="tab" href="#nav-border-exiting"
                                            role="tab" aria-selected="false" tabindex="-1">
                                            @lang("translation.leads.update_existing")
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content text-muted">
                                    <div class="tab-pane active show" id="nav-border-add" role="tabpanel">
                                        <form action="{{ route('admin.leads.store-student') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="lead_id" value="{{ $lead?->id }}">
                                            <div class="mb-3">
                                                <label for="name">@lang("translation.students.name")</label>
                                                <input type="text" id="name" class="form-control" name="name" required
                                                    value="{{  $lead->name ?? '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="surname">@lang("translation.students.surname")</label>
                                                <input type="text" id="surname" class="form-control" name="surname"
                                                    required>
                                            </div>
                                            @if(isset($lead->phone))
                                            <div class="mb-3 form-group lead-phone">
                                                <label for="leadPhone">@lang("translation.students.phone")</label>
                                                <input type="tel" id="leadPhone" name="phone" autocomplete="off"
                                                    class="form-control phone" required placeholder="Phone"
                                                    wire:model="selectedTaskPhone">
                                            </div>
                                            @endif
                                            <div class="mb-3">
                                                <button class="btn btn-success w-100" type="submit">
                                                    <i class="fas fa-save"></i> @lang("translation.btn.save")
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane" id="nav-border-exiting" role="tabpanel">
                                        <div class="mb-3">
                                            <label for="student">@lang("translation.students.students")</label>
                                            <select class="form-select students-select" id="student" name="student">
                                                {{-- @foreach($students as $student)--}}
                                                {{-- <option value="{{ $student->id }}">--}}
                                                    {{-- {{ $student->getFullName() }}--}}
                                                    {{-- | {{ $student->getFormattedPhone() }}--}}
                                                    {{-- </option>--}}
                                                {{-- @endforeach--}}
                                            </select>
                                            <span class="text-danger">* Temporary disabled</span>
                                        </div>
                                        <div class="mb-3">
                                            <button class="btn btn-success w-100" disabled>
                                                <i class="fas fa-save"></i> @lang("translation.btn.save")
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if(isset($reminders))
                <div class="mb-3">
                    <h6>@lang("translation.leads.reminders")
                        <span class="badge bg-success-subtle text-success">
                            {{isset($reminders) ? count($reminders) : 0}}
                        </span>
                    </h6>
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th>ID</th>
                                <th>{{__("translation.leads.date")}}</th>
                                <th>{{__("translation.leads.time")}}</th>
                                <th>{{__("translation.leads.comment")}}</th>
                                <th>{{__("attr.status")}}</th>
                                <th>{{__("translation.students.created_by")}}</th>
                                <th>{{__("attr.created_at")}}</th>
                                <th>{{__("translation.actions")}}</th>
                            </tr>
                            @if($reminders->count())
                            @foreach($reminders as $reminder)
                            <tr>
                                <td>{{$reminder->id}}</td>
                                <td>{{dateFormat($reminder->reminder_date)}}</td>
                                <td>{{dateFormat($reminder->reminder_time, 'H:i')}}</td>
                                <td>{{$reminder->reminder_note}}</td>
                                <td>
                                    <span @class(['badge', 'bg-success-subtle text-success'=> $reminder->reminder_status
                                        == 'completed',
                                        'bg-danger-subtle text-danger' => $reminder->reminder_status == 'pending'
                                        ])>
                                        {{__("translation.leads.".$reminder->reminder_status)}}
                                    </span>
                                </td>
                                <td>{{$reminder->causer->name ?? '-'}}</td>
                                <td>{{dateFormat($reminder->created_at, 'd.m.Y H:i')}}</td>
                                <td>
                                    @if($reminder->reminder_status == 'pending')
                                    <form action="{{ route('admin.leads.update-reminder', $reminder->id) }}"
                                        method="POST" class="d-inline-block">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" id="status" value="completed">
                                        <button class="btn bg-success-subtle text-success btn-sm">
                                            {{__("translation.leads.complete")}} <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="badge bg-success-subtle text-success">
                                        {{__("translation.leads.completed")}}
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="100" class="text-center">
                                    {{__("translation.no_data")}}
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                @endif
                <div class="mb-3">
                    <h6>@lang("translation.leads.calls")
                        <span class="badge bg-success-subtle text-success">{{$calls ? count($calls) : 0}}</span>
                    </h6>
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th>ID</th>
                                <th>{{__("translation.leads.causer")}}</th>
                                <th>{{__("attr.status")}}</th>
                                <th>{{__("translation.leads.comment")}}</th>
                                <th>{{__("translation.leads.date")}}</th>
                            </tr>
                            @if($calls && $calls->count())
                            @foreach($calls as $call)
                            <tr>
                                <td>{{$call->id}}</td>
                                <td>{{$call->causer->name ?? 'Unknown'}}</td>
                                <td>
                                    <span @class(['badge', 'bg-success-subtle text-success'=> $call->status ==
                                        'answered',
                                        'bg-danger-subtle text-danger' => $call->status == 'declined'
                                        ])>
                                        {{__("translation.leads.".$call->status)}}
                                    </span>
                                </td>
                                <td>{{$call->comment}}</td>
                                <td>{{dateFormat($call->created_at, 'd.m.Y H:i')}}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="100" class="text-center">
                                    {{__("translation.no_data")}}
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-preloader d-none" wire:loading.class.remove="d-none">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
    @push('custom-scripts')
    @script
    <script>
        $wire.on('task-clicked', () => {
                console.log('Task clicked');
                $('#leadModal').modal('show');
            });

            $wire.on('task-removed', (event) => {
                $('#leadModal').modal('hide');
            });

            $(document).ready(function () {
                $('.leadStudentShowButton').click(function () {
                    $('#leadStudent').toggleClass('d-none');
                    $('.leadStudentShowButton').addClass('d-none');
                    $('.students-select').select2({
                        placeholder: 'Select an option'
                    });
                });
                $('#teachers').select2({
                    placeholder: "@lang('translation.leads.select_teacher')",
                    allowClear: true,
                    multiple: true,
                    closeOnSelect: false,
                });
                $('#groupSwitch').change(function () {
                    if ($(this).is(':checked')) {
                        $('#group-container').removeClass('d-none');
                    } else {
                        $('#group-container').addClass('d-none');
                    }
                });
            });
    </script>
    @endscript
    @endpush
</div>
