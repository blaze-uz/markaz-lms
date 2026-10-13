<div class="col-md-8">
    @if($item->students->count() > 0)
    <!-- Group Cards -->
    @include('admin.groups.show.cards')
    @endif
    @if($item->students->count() > 0)
    <div class="card rounded-4">
        <div class="card-body px-3 py-3">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs nav-justified border-bottom mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active px-3" data-bs-toggle="tab" href="#students" role="tab" aria-selected="true">
                        <i class="ri-team-line align-middle me-1"></i>
                        @lang("translation.students.students")
                    </a>
                </li>
                @if($item->state == 'active' || $item->state == 'completed')
                <li class="nav-item" role="presentation">
                    <a class="nav-link px-3" data-bs-toggle="tab" href="#debts" role="tab" aria-selected="false"
                        tabindex="-1">
                        <i class="ri-money-dollar-circle-line align-middle me-1"></i>
                        @lang("attr.debts")
                    </a>
                </li>
                @endif
                <li class="nav-item" role="presentation">
                    <a class="nav-link px-3" data-bs-toggle="tab" href="#timeline1" role="tab" aria-selected="false"
                        tabindex="-1">
                        <i class="ri-time-line align-middle me-1"></i>
                        @lang("translation.groups.timeline")
                    </a>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane active show" id="students" role="tabpanel">
                    <livewire:group.students-list :group_id="$item->id" />
                </div>
                @if($item->state == 'active' || $item->state == 'completed')
                <div class="tab-pane" id="debts" role="tabpanel">
                    <livewire:group.debts-table :group="$item" />
                </div>
                @endif
                <div class="tab-pane" id="timeline1" role="tabpanel">
                    <table class="table">
                        <tr>
                            <th class="py-2"><i class="ri-calendar-line me-1"></i> @lang("attr.day")</th>
                            <th class="py-2"><i class="ri-time-line me-1"></i> @lang("attr.start_time")</th>
                            <th class="py-2"><i class="ri-time-line me-1"></i> @lang("attr.end_time")</th>
                            <th class="py-2"><i class="ri-building-line me-1"></i> @lang("attr.room")</th>
                            <th class="py-2"></th>
                        </tr>
                        <tr>
                            <th class="py-2">
                                <i class="ri-calendar-2-line me-1"></i> @lang("attr.monday")
                            </th>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'monday')->value('start') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'monday')->value('end') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-door-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'monday')->first()->room->name ?? '-' }}
                            </td>
                            <td class="py-2"></td>
                        </tr>
                        <tr>
                            <th class="py-2">
                                <i class="ri-calendar-2-line me-1"></i> @lang("attr.tuesday")
                            </th>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'tuesday')->value('start') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'tuesday')->value('end') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-door-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'tuesday')->first()->room->name ?? '-' }}
                            </td>
                            <td class="py-2"></td>
                        </tr>
                        <tr>
                            <th class="py-2">
                                <i class="ri-calendar-2-line me-1"></i> @lang("attr.wednesday")
                            </th>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'wednesday')->value('start') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'wednesday')->value('end') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-door-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'wednesday')->first()->room->name ?? '-' }}
                            </td>
                            <td class="py-2"></td>
                        </tr>
                        <tr>
                            <th class="py-2">
                                <i class="ri-calendar-2-line me-1"></i> @lang("attr.thursday")
                            </th>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'thursday')->value('start') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'thursday')->value('end') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-door-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'thursday')->first()->room->name ?? '-' }}
                            </td>
                            <td class="py-2"></td>
                        </tr>
                        <tr>
                            <th class="py-2">
                                <i class="ri-calendar-2-line me-1"></i> @lang("attr.friday")
                            </th>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'friday')->value('start') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'friday')->value('end') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-door-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'friday')->first()->room->name ?? '-' }}
                            </td>
                            <td class="py-2"></td>
                        </tr>
                        <tr>
                            <th class="py-2">
                                <i class="ri-calendar-2-line me-1"></i> @lang("attr.saturday")
                            </th>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'saturday')->value('start') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'saturday')->value('end') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-door-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'saturday')->first()->room->name ?? '-' }}
                            </td>
                            <td class="py-2"></td>
                        </tr>
                        <tr>
                            <th class="py-2">
                                <i class="ri-calendar-2-line me-1"></i> @lang("attr.sunday")
                            </th>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'sunday')->value('start') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-time-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'sunday')->value('end') ?? '-' }}
                            </td>
                            <td class="py-2">
                                <i class="ri-door-line text-muted me-1"></i>{{ $item->groupTimes->where('name', 'sunday')->first()->room->name ?? '-' }}
                            </td>
                            <td class="py-2"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div><!-- end card-body -->
    </div>
    @else
    @if($item->state == 'active' || $item->state == 'demo')
    <div class="card card-height-100">
        <div class="card-body">
            <div class="d-flex justify-content-center align-items-center flex-column h-100">
                <lord-icon src="https://cdn.lordicon.com/kcoawjon.json" trigger="loop" delay="2000"
                    style="width:150px; height:150px">
                </lord-icon>
                <h3 class="mb-3">@lang("translation.groups.no_students")!</h3>
                <form action="{{ route('admin.groups.attach-student.view') }}" class="mb-3">
                    <input type="hidden" name="group_id" value="{{$item->id}}">
                    <button type="submit" class="btn btn-success w-100">
                        <span class="mdi mdi-attachment"></span>
                        @lang("translation.groups.attach_student")
                    </button>
                </form>
            </div>
        </div>
    </div>
    @else
    <div class="card card-height-100">
        <div class="card-body">
            <div class="d-flex justify-content-center align-items-center flex-column h-100">
                <lord-icon src="https://cdn.lordicon.com/urmrbzpi.json" trigger="loop" delay="2000"
                    style="width:150px; height:150px">
                </lord-icon>
                <h3 class="mb-3">@lang("translation.groups.completed")!</h3>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>
