<div class="col-md-4">
    <div class="card shadow-sm rounded-4">
        <div class="card-header rounded-top-4">
            <div class="px-3 py-2 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">
                        {{ $item->name }}
                        <x-groups.header-badges :item="$item" />
                    </h5>
                    <p class="mb-0 text-muted small">
                        <i class="ri-time-line me-1"></i>
                        @lang('attr.create_date') {{ dateFormat($item->created_at) }}
                    </p>
                </div>
                <x-groups.action-dropdown :item="$item" />
            </div>
        </div>
        <div class="card-body px-3 py-3">
            <div class="table-responsive">
                <table class="table mb-0">
                    <tbody>
                        <tr>
                            <th width="40%" class="border-0 py-2">
                                <div class="d-flex align-items-center">
                                    <i class="ri-user-star-line text-primary me-2"></i>
                                    @lang('translation.groups.teacher')
                                </div>
                            </th>
                            <td class="border-0 py-2">
                                <a href="{{ route('admin.users.show', $item->teacher->id ?? 0) }}"
                                    class="text-decoration-none">
                                    {{ $item->teacher?->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th class="border-0 py-2">
                                <div class="d-flex align-items-center">
                                    <i class="ri-book-open-line text-primary me-2"></i>
                                    @lang('translation.groups.course')
                                </div>
                            </th>
                            <td class="border-0 py-2">{{ $item->course?->name }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 py-2">
                                <div class="d-flex align-items-center">
                                    <i class="ri-git-branch-line text-primary me-2"></i>
                                    @lang('translation.groups.branch')
                                </div>
                            </th>
                            <td class="border-0 py-2">
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ $item->branches()->first()->name }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="border-0 py-2">
                                <div class="d-flex align-items-center">
                                    <i class="ri-money-dollar-circle-line text-primary me-2"></i>
                                    @lang('translation.groups.amount')
                                </div>
                            </th>
                            <td class="border-0 py-2">{{ money($item->price) }}</td>
                        </tr>
                        @php
                            $teacherPercentage = $item->teacher_percentage ?? ($item->teacher?->percentage ?? 0);
                        @endphp
                        @if ($teacherPercentage > 0)
                            <tr>
                                <th class="border-0 py-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-percent-line text-primary me-2"></i>
                                        @lang('translation.groups.teacher_percentage')
                                    </div>
                                </th>
                                <td class="border-0 py-2">{{ $teacherPercentage }}%</td>
                            </tr>
                        @endif
                        <tr>
                            <th class="border-0 py-2">
                                <div class="d-flex align-items-center">
                                    <i class="ri-bank-card-line text-primary me-2"></i>
                                    @lang('translation.account')
                                </div>
                            </th>
                            <td class="border-0 py-2">
                                <a href="{{ route('admin.accounts.show', $item->account->id ?? 0) }}"
                                    class="text-decoration-none">
                                    {{ $item->account->name ?? '-' }}
                                </a>
                            </td>
                        </tr>
                        @if ($item->state != 'completed')
                            <tr>
                                <th class="border-0 py-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-calendar-line text-primary me-2"></i>
                                        @lang('translation.groups.current_debt_range')
                                    </div>
                                </th>
                                <td class="border-0 py-2">{{ $item->getPaymentDate() }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <div class="row g-2">
                    <div class="col-6">
                        <a class="btn btn-warning w-100" href="{{ route('admin.groups.index') }}">
                            <i class="mdi mdi-arrow-left me-1"></i>
                            @lang('translation.btn.back')
                        </a>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-light w-100" data-bs-toggle="modal"
                            data-bs-target="#moreInfoModal">
                            <i class="ri-information-line me-1"></i>
                            @lang('translation.more')
                        </button>
                    </div>
                </div>

                @can('group-attendance')
                    @if ($item->students->count() && $item->state == 'active')
                        <div class="mt-2">
                            @if ($todayAttendanceExists)
                                @can('group-attendance-custom-date')
                                    <div class="dropdown">
                                        <button class="btn btn-primary w-100 dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="mdi mdi-table-account me-1"></i>
                                            @lang('translation.btn.attendance')
                                        </button>
                                        <ul class="dropdown-menu w-100">
                                            <li>
                                                <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                    data-bs-target="#attendanceModal">
                                                    @lang('translation.btn.add_attendance')
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.attendance.edit', ['group' => $item->id]) }}">
                                                    @lang('translation.btn.edit_attendance')
                                                    ({{ dateFormat(now()) }})
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    <a class="btn btn-primary w-100"
                                        href="{{ route('admin.attendance.edit', ['group' => $item->id]) }}">
                                        <i class="mdi mdi-table-account me-1"></i>
                                        @lang('translation.btn.edit_attendance') ({{ dateFormat(now()) }})
                                    </a>
                                @endif
                            @else
                                @can('group-attendance-custom-date')
                                    <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#attendanceModal">
                                        <i class="mdi mdi-table-account me-1"></i>
                                        @lang('translation.btn.attendance')
                                    </button>
                                @else
                                    <a class="btn btn-primary w-100"
                                        href="{{ route('admin.attendance', ['group' => $item->id]) }}">
                                        <i class="mdi mdi-table-account me-1"></i>
                                        @lang('translation.btn.attendance')
                                    </a>
                            @endif
                            @endif
                        </div>
                    @else
                        <div class="mt-2">
                            <button class="btn btn-primary w-100" disabled>
                                <i class="mdi mdi-table-account me-1"></i>
                                @lang('translation.btn.attendance')
                            </button>
                        </div>
                        @endif
                    @endcan

                    @if ($item->state == 'completed')
                        @can('group-delete')
                            <div class="mt-2">
                                <form action="{{ route('admin.groups.destroy', $item->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure?')">
                                        <i class="mdi mdi-delete me-1"></i>
                                        @lang('translation.btn.delete')
                                    </button>
                                </form>
                            </div>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
        <!--end card-->
        <!-- Complete Group Modal -->
        <div class="modal fade" id="groupCompleteModal" tabindex="-1" aria-labelledby="groupCompleteModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="groupCompleteModalLabel">
                            @lang('translation.groups.complete_group')
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.groups.complete', $item->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="w-full d-flex justify-content-center align-items-center">
                                <lord-icon src="https://cdn.lordicon.com/otnkfjhb.json" trigger="hover" state="loop-cycle"
                                    style="width:100px;height:100px">
                                </lord-icon>
                            </div>
                            <h4 class="mt-3 text-center">
                                @lang('translation.groups.complete_group_text')
                            </h4>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                @lang('translation.Close')
                            </button>
                            <button type="submit" class="btn btn-danger">
                                @lang('translation.groups.complete_group')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
