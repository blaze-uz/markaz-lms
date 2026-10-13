<div>
    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
        <style>
            .noTaskText {
                display: none; /* Hide the element by default */
            }

            .noTaskText:only-child {
                display: block; /* Display the element if it's the only child of its parent */
            }

            /* Remove the dropdown caret in a Bootstrap dropdown */
            .dropdown-toggle::after {
                display: none !important;
                content: '' !important;
            }
        </style>
    @endpush
    <div class="overflow-auto">
        <div class="d-flex align-items-stretch gap-2 overflow-x-auto" style="width: max-content">
            @foreach($collections as $collection)
                <div style="width: 411px">
                    <div class="d-flex justify-content-between">
                        <h4>{{$collection->name}} <span class="badge bg-secondary">{{count($collection->unsignedTasks)}}</span></h4>
                        <div class="px-3">
                            <button class="btn" style="background-color: #C5DFF8" data-bs-toggle="modal"
                                    title="{{__('translation.leads.add')}}"
                                    wire:click="openTaskModal({{$collection->id}})" data-bs-target="#createTaskModal">
                                <i class="fa-solid fa-user-plus"></i>
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal"
                                    title="{{__('translation.leads.create_board')}}"
                                    wire:click="openBoardModal({{$collection->id}})" data-bs-target="#createBoardModal">
                                <i class="fa-solid fa-chalkboard-user"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card my-3 cursor-pointer user-select-none">
                        <div class="card-body group-container collection-content"
                             data-collection-id="{{$collection->id}}">
                            <span class="noTaskText text-center fs-4 text-muted p-3">
                                Empty
                            </span>
                            @foreach($collection->unsignedTasks as $task)
                                <div class="card content my-2" style="background-color: #FBEEAC"
                                     wire:click="clickTask({{$task->id}})"
                                     data-type="task"
                                     data-task-board-id="{{$task->board->id ?? 0}}"
                                     data-task-collection-id="{{$collection->id}}"
                                     id="{{$task->id}}">
                                    <div class="card-body task">
                                        <span>{{$task->name}} | {{$task->phone}}</span>
                                        <div class="d-flex justify-content-between mt-2">
                                            <div>
                                                @if($task->comment)
                                                    <span class="badge bg-info-subtle text-info">
                                                    <i class="fa-solid fa-align-left"></i>
                                                </span>
                                                @endif
                                            </div>
                                            <span class="badge bg-info-subtle text-info">
                                                <i class="fa-regular fa-clock"></i> {{dateFormat($task->created_at)}}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @foreach($collection->boards as $board)
                                <div class="card container-item my-2" style="background-color: #C5DFF8"
                                     data-type="board"
                                     data-board-id="{{$board->id}}"
                                     @if($board->url)
                                         data-url="{{ route('lead.form', $board->url) }}"
                                        @endif
                                >
                                    <div class="card-title d-flex justify-content-between px-3 pt-2">
                                        <span>{{$board->name}} <span class="badge bg-secondary">{{count($board->tasks)}}</span></span>
                                        <a href="{{ route('admin.boards.show', $board->id) }}" class="btn btn-sm"
                                           target="_blank">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-muted fs-6"></i>
                                        </a>
                                    </div>
                                    <div class="card-body pt-1 px-2 noContainerItem">
                                        <div class="group-container border border-dashed p-1"
                                             data-type="board"
                                             data-board-id="{{$board->id}}"
                                             data-collection-id="{{$board->kanban_collection_id}}">
                                            <span class="noTaskText text-center fs-4 text-muted p-3">
                                                Empty
                                            </span>
                                            @foreach($board->tasks as $board_task)
                                                <div class="card content my-2 task" id="{{$board_task->id}}"
                                                     style="background-color: #FBEEAC"
                                                     data-type="task"
                                                     data-task-board-id="{{$board->id}}"
                                                     data-task-collection-id="{{$collection->id}}"
                                                     wire:click="clickTask({{$board_task->id}})">
                                                    <div class="card-body">
                                                        <span>{{$board_task->name}} | {{$board_task->phone}}</span>
                                                        <div class="d-flex justify-content-between mt-2">
                                                            <div>
                                                                @if($board_task->comment)
                                                                    <span class="badge bg-info-subtle text-info">
                                                                        <i class="fa-solid fa-align-left"></i>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <span class="badge bg-info-subtle text-info">
                                                                <i class="fa-regular fa-clock"></i> {{dateFormat($board_task->created_at)}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
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
            @endforeach
        </div>
    </div>

    <!-- Create Board Modal -->
    <div wire:ignore.self class="modal fade" id="createBoardModal" tabindex="-1" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">@lang("translation.leads.create_board")</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="storeBoard">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="board-name">@lang("translation.leads.board_name")</label>
                            <input id="board-name" type="text"
                                   class="form-control @error('boardName') is-invalid @enderror"
                                   placeholder="{{__('translation.leads.board_name')}}" wire:model="boardName">
                            @error('boardName')
                            <div class="invalid-feedback">
                                {{$message}}
                            </div>
                            @enderror
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="groupSwitch" wire:model="groupSwitch" name="groupSwitch">
                            <label class="form-check-label" for="groupSwitch">
                                @lang("translation.leads.attach_group")
                            </label>
                        </div>
                        <div class="form-group mb-3 d-none" id="group-container">
                            <label for="groupId">
                                @lang("translation.leads.select_group")
                            </label>
                            <select name="groupId" id="groupId" class="form-control" wire:model="groupId">
                                <option value="">@lang("translation.leads.select_group")</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">@lang("translation.Close")</button>
                        <button type="submit" class="btn btn-primary">@lang("translation.btn.save")</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Creat Task Modal -->
    <div wire:ignore.self class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">@lang("translation.leads.create_lead")</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="storeTask">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="board-name">@lang("translation.leads.name") <span
                                        class="text-danger">*</span></label>
                            <input id="board-name" type="text"
                                   autocomplete="off"
                                   class="form-control @error('taskName') is-invalid @enderror"
                                   placeholder="{{__('translation.leads.name')}}" wire:model="taskName">
                            @error('taskName')
                            <div class="invalid-feedback">
                                {{$message}}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="taskPhone">@lang("translation.leads.phone") <span
                                        class="text-danger">*</span></label>
                            <input type="tel" id="taskPhone" autocomplete="off"
                                   wire:model="taskPhone"
                                   class="form-control phone @error('taskPhone') is-invalid @enderror"
                                   placeholder="{{__('translation.leads.phone')}}">
                            @error('taskPhone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <label for="chosen_days">
                                        @lang("translation.leads.chosen_days")
                                    </label>
                                    <div class="d-flex justify-content-start gap-2">
                                        <div class="mr-3">
                                            <input type="radio" class="btn-check" name="chosen_days"
                                                   wire:model="chosenDays"
                                                   id="primary-outlined" value="odd">
                                            <label class="btn btn-outline-primary"
                                                   for="primary-outlined">{{__("translation.leads.odd")}}</label>
                                        </div>
                                        <div>
                                            <input type="radio" class="btn-check"
                                                   wire:model="chosenDays"
                                                   name="chosen_days"
                                                   id="danger-outlined" value="even">
                                            <label class="btn btn-outline-primary"
                                                   for="danger-outlined">{{__("translation.leads.even")}}
                                            </label>
                                        </div>
                                        @error('chosen_days')
                                        <div class="text-danger">* {{$message}}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label for="chosen_time">
                                        @lang("translation.leads.chosen_time")
                                    </label>
                                    <div class="input-group">
                                        <input type="time" class="form-control" id="chosen_time"
                                               wire:model="chosenTime">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="teachers">
                                @lang("translation.users.select_teacher")
                            </label>
                            <select name="teachers[]" id="teachers" class="form-control" wire:model="chosenTeachers">
                                <option value="">@lang("translation.leads.select_teacher")</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}"
                                            @if(isset($item) && $item->teacheres->contains($teacher->id)) selected @endif>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <label for="taskComment">
                            @lang("translation.leads.comment")
                        </label>
                        <textarea class="form-control" wire:model="taskComment" id="taskComment"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">@lang("translation.Close")</button>
                        <button type="submit" class="btn btn-primary">@lang("translation.btn.save")</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Lead Modal -->
{{--    <div wire:ignore.self class="modal fade" id="leadModal" tabindex="-1" aria-labelledby="exampleModalLabel"--}}
{{--         aria-hidden="true">--}}
{{--        <div class="modal-dialog modal-lg">--}}
{{--            <div class="modal-content">--}}
{{--                <div class="modal-header">--}}
{{--                    <h1 class="modal-title fs-5" id="exampleModalLabel">@lang("translation.leads.lead")</h1>--}}
{{--                    <div class="d-flex justify-content-between gap-1 align-items-center">--}}
{{--                        <!-- Dropdown Menu Item Color -->--}}
{{--                        <div class="btn-group">--}}
{{--                            <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown"--}}
{{--                                    aria-expanded="false">--}}
{{--                                <i class="fa-solid fa-ellipsis text-muted"></i>--}}
{{--                            </button>--}}
{{--                            <div class="dropdown-menu dropdownmenu-primary">--}}
{{--                                <a class="dropdown-item" href="{{ route('admin.leads.edit', $lead?->id ?? 0) }}">--}}
{{--                                    @lang("attr.edit")--}}
{{--                                </a>--}}
{{--                                <button class="dropdown-item" wire:click="removeTask({{$lead?->id}})">--}}
{{--                                    @lang("translation.btn.delete")--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div><!-- btn-group -->--}}
{{--                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="modal-body lead-form">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-md-7 col-sm-12">--}}
{{--                            <div class="form-group mb-3 input-group-lg">--}}
{{--                                <input type="text" class="form-control border-0 p-0" value="{{$lead?->name}}"--}}
{{--                                       wire:model="taskModalName"--}}
{{--                                       wire:change.stop="changeTaskName({{$lead}})">--}}
{{--                            </div>--}}
{{--                            <div>--}}
{{--                                <small class="text-muted">--}}
{{--                                    @lang("translation.leads.phone")--}}
{{--                                </small>--}}
{{--                                <p>--}}
{{--                                    {{ $lead?->phone }}--}}
{{--                                </p>--}}
{{--                            </div>--}}
{{--                            <div class="mb-3">--}}
{{--                                @if($lead?->chosen_days || $lead?->chosen_time)--}}
{{--                                    <small class="text-muted">@lang("translation.leads.chosen_time")</small><br>--}}
{{--                                @endif--}}
{{--                                @if($lead?->chosen_days)--}}
{{--                                    <span class="badge bg-success-subtle text-success">--}}
{{--                                        {{ __('translation.leads.'.$lead?->chosen_days) }}--}}
{{--                                    </span>--}}
{{--                                @endif--}}
{{--                                @if($lead?->chosen_time)--}}
{{--                                    <span class="badge bg-success-subtle text-success">--}}
{{--                                        {{dateFormat($lead?->chosen_time, 'H:i')}}--}}
{{--                                    </span>--}}
{{--                                @endif--}}
{{--                            </div>--}}
{{--                            @if($lead?->teachers->count())--}}
{{--                                <small class="text-muted">--}}
{{--                                    @lang("translation.leads.teachers")--}}
{{--                                </small>--}}
{{--                                <p>--}}
{{--                                    @foreach($lead?->teachers as $teacher)--}}
{{--                                        {{ $teacher->name }}--}}
{{--                                    @endforeach--}}
{{--                                </p>--}}
{{--                            </div>--}}
{{--                            @if($lead?->comment)--}}
{{--                                <small class="text-muted">--}}
{{--                                    @lang("translation.leads.comment")--}}
{{--                                </small>--}}
{{--                                <p>--}}
{{--                                    {{ $lead?->comment }}--}}
{{--                                </p>--}}
{{--                            </div>--}}
{{--                            <div>--}}
{{--                                <form action="{{ route('admin.leads.add-comment') }}" class="w-100 d-flex gap-2"--}}
{{--                                      method="POST">--}}
{{--                                    @csrf--}}
{{--                                    <div class="w-100">--}}
{{--                                        <input type="hidden" name="task_id" value="{{ $lead?->id }}">--}}
{{--                                        <input type="text" placeholder="{{__('translation.leads.comment')}}"--}}
{{--                                               name="comment"--}}
{{--                                               class="form-control @error('comment') is-invalid @enderror">--}}
{{--                                    </div>--}}
{{--                                    <button class="btn btn-success" type="submit">--}}
{{--                                        <i class="fas fa-plus"></i>--}}
{{--                                    </button>--}}
{{--                                </form>--}}
{{--                                @error('comment')--}}
{{--                                <div class="text-danger">--}}
{{--                                    {{ $message }}--}}
{{--                                </div>--}}
{{--                                @enderror--}}
{{--                            </div>--}}
{{--                            <div class="mt-5">--}}
{{--                                <h6>@lang("translation.leads.activity") <small--}}
{{--                                            class="text-muted">@lang("translation.leads.last_10_records")</small></h6>--}}
{{--                                <div class="d-flex flex-column gap-3">--}}
{{--                                    @if($activities)--}}
{{--                                        @foreach($activities as $activity)--}}
{{--                                            <div class="d-flex justify-content-between flex-column mb-3">--}}
{{--                                                <div>--}}
{{--                                                    <span class="fs-5">{{$lead?->name}}</span>--}}
{{--                                                    @if($activity->description == 'created')--}}
{{--                                                        @lang("translation.leads.created") <span--}}
{{--                                                                class="text-muted">{{ $activity->created_at->diffForHumans() }}</span>--}}
{{--                                                    @else--}}
{{--                                                        @lang("translation.leads.updated")--}}
{{--                                                        @if ($activity->description == 'updated')--}}
{{--                                                            &nbsp; <span--}}
{{--                                                                    class="text-muted">{{ $activity->created_at->diffForHumans() }}</span>--}}
{{--                                                            <br>--}}
{{--                                                            @php--}}
{{--                                                                $changes = json_decode($activity->properties, true);--}}
{{--                                                                $oldValues = $changes['old'] ?? [];--}}
{{--                                                                $newValues = $changes['attributes'] ?? [];--}}

{{--                                                                foreach ($newValues as $key => $newValue) {--}}
{{--                                                                    $oldValue = $oldValues[$key] ?? null;--}}
{{--                                                                    if($key == 'kanban_collection_id'){--}}
{{--                                                                        $oldValue = \App\Models\KanbanCollection::find($oldValue)->name ?? 'None';--}}
{{--                                                                        $newValue = \App\Models\KanbanCollection::find($newValue)->name ?? 'None';--}}
{{--                                                                    }--}}
{{--                                                                    if ($key == 'kanban_board_id') {--}}
{{--                                                                        $oldValue = \App\Models\KanbanBoard::find($oldValue)->name ?? 'None';--}}
{{--                                                                        $newValue = \App\Models\KanbanBoard::find($newValue)->name ?? 'None';--}}
{{--                                                                    }--}}
{{--                                                                    if ($key == 'attend') {--}}
{{--                                                                        $oldValue = $oldValue ? __('translation.leads.attended') : __('translation.leads.not_attended');--}}
{{--                                                                        $newValue = $newValue ? __('translation.leads.attended') : __('translation.leads.not_attended');--}}
{{--                                                                    }--}}
{{--                                                                    if ($oldValue !== $newValue) {--}}
{{--                                                                        echo "$oldValue &nbsp <span class='badge bg-success-subtle text-success'><i class='fas fa-arrow-right'></i></span> &nbsp $newValue<br>";--}}
{{--                                                                    }--}}
{{--                                                                }--}}
{{--                                                            @endphp--}}
{{--                                                        @endif--}}
{{--                                                    @endif--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        @endforeach--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="col-md-5 col-sm-12">--}}
{{--                            <div class="d-flex justify-content-end flex-column">--}}
{{--                                <button class="btn btn-success leadStudentShowButton">--}}
{{--                                    <i class="fas fa-plus"></i> @lang("translation.leads.add_as_student")--}}
{{--                                </button>--}}
{{--                                <div class="form-group d-none" id="leadStudent">--}}
{{--                                    <ul class="nav nav-tabs nav-border-top nav-border-top-success mb-3" role="tablist">--}}
{{--                                        <li class="nav-item" role="presentation">--}}
{{--                                            <a class="nav-link active text-success" data-bs-toggle="tab"--}}
{{--                                               href="#nav-border-add"--}}
{{--                                               role="tab" aria-selected="true">--}}
{{--                                                @lang("translation.leads.add")--}}
{{--                                            </a>--}}
{{--                                        </li>--}}
{{--                                        <li class="nav-item" role="presentation">--}}
{{--                                            <a class="nav-link text-success" data-bs-toggle="tab"--}}
{{--                                               href="#nav-border-exiting"--}}
{{--                                               role="tab" aria-selected="false" tabindex="-1">--}}
{{--                                                @lang("translation.leads.update_existing")--}}
{{--                                            </a>--}}
{{--                                        </li>--}}
{{--                                    </ul>--}}
{{--                                    <div class="tab-content text-muted">--}}
{{--                                        <div class="tab-pane active show" id="nav-border-add" role="tabpanel">--}}
{{--                                            <form action="{{ route('admin.leads.store-student') }}" method="POST">--}}
{{--                                                @csrf--}}
{{--                                                <input type="hidden" name="lead_id" value="{{ $lead?->id }}">--}}
{{--                                                <div class="mb-3">--}}
{{--                                                    <label for="name">@lang("translation.students.name")</label>--}}
{{--                                                    <input type="text" id="name" class="form-control" name="name"--}}
{{--                                                           required--}}
{{--                                                           value="{{  $lead->name ?? '' }}">--}}
{{--                                                </div>--}}
{{--                                                <div class="mb-3">--}}
{{--                                                    <label for="surname">@lang("translation.students.surname")</label>--}}
{{--                                                    <input type="text" id="surname" class="form-control" name="surname"--}}
{{--                                                           required>--}}
{{--                                                </div>--}}
{{--                                                @if(isset($lead->phone))--}}
{{--                                                    <div class="form-group mb-3 lead-phone">--}}
{{--                                                        <label for="leadPhone">@lang("translation.students.phone")</label>--}}
{{--                                                        <input type="tel" name="phone" autocomplete="off"--}}
{{--                                                               class="form-control"--}}
{{--                                                               required wire:model="selectedTaskPhone"--}}
{{--                                                               placeholder="Phone">--}}
{{--                                                    </div>--}}
{{--                                                @endif--}}
{{--                                                <div class="mb-3">--}}
{{--                                                    <button class="btn btn-success w-100" type="submit">--}}
{{--                                                        <i class="fas fa-save"></i> @lang("translation.btn.save")--}}
{{--                                                    </button>--}}
{{--                                                </div>--}}
{{--                                            </form>--}}
{{--                                        </div>--}}
{{--                                        <div class="tab-pane" id="nav-border-exiting" role="tabpanel">--}}
{{--                                            <div class="mb-3">--}}
{{--                                                <label for="student">@lang("translation.students.students")</label>--}}
{{--                                                <select class="form-select students-select" id="student" name="student">--}}
{{--                                                </select>--}}
{{--                                                <span class="text-danger">* Temporary disabled</span>--}}
{{--                                            </div>--}}
{{--                                            <div class="mb-3">--}}
{{--                                                <button class="btn btn-success w-100" disabled>--}}
{{--                                                    <i class="fas fa-save"></i> @lang("translation.btn.save")--}}
{{--                                                </button>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="mt-3">--}}
{{--                                <h6>@lang("translation.leads.comments")</h6>--}}
{{--                                @if($comments)--}}
{{--                                    @forelse($comments as $comment)--}}
{{--                                        <div class="d-flex justify-content-between flex-column mb-3">--}}
{{--                                            <div>--}}
{{--                                                <span class="fs-6">{{$comment->user->name}}</span>--}}
{{--                                                <span class="text-muted">{{$comment->created_at->diffForHumans()}}</span>--}}
{{--                                            </div>--}}
{{--                                            <div>--}}
{{--                                                💬 <span>{{$comment->comment}}</span>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    @empty--}}
{{--                                        <div class="text-muted">@lang("translation.leads.no_comments")</div>--}}
{{--                                    @endforelse--}}
{{--                                @endif--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="card-preloader d-none" wire:loading.class.remove="d-none">--}}
{{--                <div class="card-status">--}}
{{--                    <div class="spinner-border text-success">--}}
{{--                        <span class="visually-hidden">Loading...</span>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
    @include('livewire.leads.partials.lead-modal')
    @push("custom-scripts")
        <!-- Latest Sortable -->
        <script src="https://raw.githack.com/SortableJS/Sortable/master/Sortable.js"></script>
        <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
        <script src="{{asset('assets/js/inputmask/inputmask.js')}}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let selector = document.querySelectorAll(".phone");
                let im = new Inputmask("+\\9\\98(99)999-99-99", {'clearIncomplete': true, 'removeMaskOnSubmit': true});
                im.mask(selector);
            })
            var boards = [];
            window.addEventListener("load", function () {
                boards = document.querySelectorAll(".group-container");
            });
            let sortableOptions2 = {
                group: {
                    name: "sortable-list-2",
                    pull: true,
                    put: true,
                },
                filter: '.noTaskText',
                animation: 250,
                forceFallback: true,
                onMove: function (evt) {
                    return !(evt.dragged.classList.contains('container-item') && evt.to.parentNode.classList.contains("noContainerItem"));
                },
                onEnd: function (evt) {
                    let type = evt.item.dataset.type;
                    let boardId = evt.item.dataset.boardId;
                    let taskId = evt.item.id;
                    let toCollectionId = evt.to.dataset.collectionId;
                    let toBoardId = evt.to.dataset.boardId;
                    if (type === "task") {
                        @this.
                        call('moveTask', taskId, toCollectionId, toBoardId);
                    } else if (type === "board") {
                        @this.
                        call('moveBoard', boardId, toCollectionId);
                    }
                }
            };

            let containers = null;
            containers = document.querySelectorAll(".group-container");
            for (let i = 0; i < containers.length; i++) {
                // disable putting container-item inside container-item
                let sortable = new Sortable(containers[i], sortableOptions2);
            }

            document.addEventListener('livewire:initialized', () => {
                @this.
                on('board-created', (event) => {
                    $('#createBoardModal').modal('hide');
                });

                @this.
                on('task-created', (event) => {
                    $('#createTaskModal').modal('hide');
                    let selector = document.querySelectorAll(".phone");
                    let im = new Inputmask("+\\9\\98(99)999-99-99", {
                        'clearIncomplete': true,
                        'removeMaskOnSubmit': true
                    });
                    im.mask(selector);
                });

                Livewire.hook('element.init', (component) => {
                    if (component.el.classList.contains('group-container')) {
                        new Sortable(component.el, sortableOptions2);
                    }
                    if (component.el.classList.contains('lead-phone')) {
                        let selectorInside = document.querySelectorAll(".phone");
                        let im = new Inputmask("+\\9\\98(99)999-99-99", {
                            'clearIncomplete': true,
                            'removeMaskOnSubmit': true
                        });
                        im.mask(selectorInside);
                        $('.students-select').select2({
                            placeholder: 'Select an option'
                        });
                    }
                });
            });

        </script>
        <script>
            @if ($errors->any())
            window.notifyMessage("{{ $errors->first() }}", 'danger')
            @endif
        </script>
    @endpush
</div>
