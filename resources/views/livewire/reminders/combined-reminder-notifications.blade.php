<div class="dropdown topbar-head-dropdown header-item combined-reminder-notifications-root" wire:poll.60s>
    <button type="button"
        class="btn btn-icon btn-topbar rounded-circle position-relative @if ($unreadCount > 0) combined-reminder-bell--active @else btn-ghost-secondary @endif"
        id="combined-reminder-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
        aria-haspopup="true" aria-expanded="false" aria-label="@lang('translation.reminders.unified_title')">
        <i class="bx bx-bell fs-22 combined-reminder-bell-icon"></i>
        @if ($unreadCount > 0)
            <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                <span class="visually-hidden">@lang('translation.reminders.unified_title')</span>
            </span>
        @endif
    </button>
    <div class="p-0 dropdown-menu dropdown-menu-lg dropdown-menu-end" x-data="{ reminderTab: 'lead' }"
        aria-labelledby="combined-reminder-notifications-dropdown">
        <div class="dropdown-head bg-primary bg-pattern rounded-top">
            <div class="px-3 py-2">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap w-100">
                    <h6 class="m-0 text-white fs-16 fw-semibold text-truncate flex-grow-1 min-w-0">
                        @lang('translation.reminders.unified_title')
                    </h6>
                    @if ($unreadCount > 0)
                        <button type="button"
                            class="btn btn-sm fw-semibold shadow-sm combined-reminder-mark-read-btn text-primary flex-shrink-0 py-1 px-2 text-nowrap"
                            wire:click.stop="markAllAsRead" wire:loading.attr="disabled">
                            @lang('translation.reminders.mark_all_short')
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs nav-tabs-custom nav-justified combined-reminder-tabs mb-0 border-bottom" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" role="tab"
                    x-bind:class="{ 'active': reminderTab === 'lead' }"
                    x-on:click.prevent.stop="reminderTab = 'lead'"
                    x-bind:aria-selected="reminderTab === 'lead' ? 'true' : 'false'">
                    <span class="d-inline-flex align-items-center gap-1">
                        @lang('translation.reminders.tab_lead')
                        @if ($leadUnreadCount > 0)
                            <span class="badge rounded-pill bg-danger">{{ $leadUnreadCount > 99 ? '99+' : $leadUnreadCount }}</span>
                        @endif
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" role="tab"
                    x-bind:class="{ 'active': reminderTab === 'student' }"
                    x-on:click.prevent.stop="reminderTab = 'student'"
                    x-bind:aria-selected="reminderTab === 'student' ? 'true' : 'false'">
                    <span class="d-inline-flex align-items-center gap-1">
                        @lang('translation.reminders.tab_student')
                        @if ($studentUnreadCount > 0)
                            <span class="badge rounded-pill bg-danger">{{ $studentUnreadCount > 99 ? '99+' : $studentUnreadCount }}</span>
                        @endif
                    </span>
                </button>
            </li>
        </ul>

        <div class="tab-content combined-reminder-tab-content">
            <div data-simplebar style="max-height: 360px;" class="py-2 px-2">
                <div x-show="reminderTab === 'lead'" x-cloak>
                    @forelse ($dueLeadReminders as $reminder)
                        @php
                            $displayName = $reminder->lead->name ?? '-';
                            $isRead = (bool) ($reminder->reads_exists ?? false);
                        @endphp
                        <div class="text-reset notification-item d-block dropdown-item position-relative rounded mb-1 combined-reminder-row @if ($isRead) combined-reminder-row--read @endif"
                            wire:key="combined-lead-reminder-{{ $reminder->id }}"
                            wire:click="openLeadReminder({{ $reminder->id }})" role="button">
                            <div class="d-flex align-items-start gap-2">
                                <div class="flex-shrink-0 avatar-xs">
                                    <span
                                        class="avatar-title rounded-circle fs-16 @if ($isRead) bg-secondary-subtle text-secondary @else bg-warning-subtle text-warning @endif">
                                        <i class="bx bx-user-pin"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden text-start min-w-0">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold text-truncate flex-grow-1">
                                            {{ $displayName }}
                                        </h6>
                                        @if ($isRead)
                                            <span
                                                class="badge bg-secondary-subtle text-secondary text-uppercase fs-10 flex-shrink-0">
                                                @lang('translation.leads.read')
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mb-1 fs-12 text-muted text-truncate">
                                        {{ $reminder->reminder_note ?: '—' }}
                                    </p>
                                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                        <span>
                                            <i class="mdi mdi-clock-outline"></i>
                                            {{ dateFormat($reminder->reminder_date ?? '-', 'd.m.Y') }}
                                            {{ dateFormat($reminder->reminder_time ?? '-', 'H:i') }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="combined-reminder-empty d-flex flex-column align-items-center justify-content-center py-4 px-3 text-center">
                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-2"
                                style="width: 48px; height: 48px;">
                                <i class="bx bx-user-pin fs-22"></i>
                            </span>
                            <p class="mb-0 fs-13 fw-medium text-body-secondary">
                                @lang('translation.reminders.empty_lead')
                            </p>
                        </div>
                    @endforelse
                </div>

                <div x-show="reminderTab === 'student'" x-cloak>
                    @forelse ($dueStudentReminders as $reminder)
                        @php
                            $displayName = $reminder->student ? $reminder->student->getFullName() : '-';
                            $isRead = (bool) ($reminder->reads_exists ?? false);
                        @endphp
                        <div class="text-reset notification-item d-block dropdown-item position-relative rounded mb-1 combined-reminder-row @if ($isRead) combined-reminder-row--read @endif"
                            wire:key="combined-student-reminder-{{ $reminder->id }}"
                            wire:click="openStudentReminder({{ $reminder->id }})" role="button">
                            <div class="d-flex align-items-start gap-2">
                                <div class="flex-shrink-0 avatar-xs">
                                    <span
                                        class="avatar-title rounded-circle fs-16 @if ($isRead) bg-secondary-subtle text-secondary @else bg-info-subtle text-info @endif">
                                        <i class="bx bx-book-reader"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden text-start min-w-0">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold text-truncate flex-grow-1">
                                            {{ $displayName }}
                                        </h6>
                                        @if ($isRead)
                                            <span
                                                class="badge bg-secondary-subtle text-secondary text-uppercase fs-10 flex-shrink-0">
                                                @lang('translation.leads.read')
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mb-1 fs-12 text-muted text-truncate">
                                        {{ $reminder->reminder_note ?: '—' }}
                                    </p>
                                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                        <span>
                                            <i class="mdi mdi-clock-outline"></i>
                                            {{ dateFormat($reminder->reminder_date ?? '-', 'd.m.Y') }}
                                            {{ dateFormat($reminder->reminder_time ?? '-', 'H:i') }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="combined-reminder-empty d-flex flex-column align-items-center justify-content-center py-4 px-3 text-center">
                            <span class="avatar-title rounded-circle bg-info-subtle text-info d-inline-flex align-items-center justify-content-center mb-2"
                                style="width: 48px; height: 48px;">
                                <i class="bx bx-book-reader fs-22"></i>
                            </span>
                            <p class="mb-0 fs-13 fw-medium text-body-secondary">
                                @lang('translation.reminders.empty_student')
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <style>
        .combined-reminder-notifications-root [x-cloak] {
            display: none !important;
        }

        .combined-reminder-bell--active {
            color: var(--vz-danger, #dc3545) !important;
            background-color: rgba(var(--vz-danger-rgb, 220, 53, 69), 0.12) !important;
            box-shadow: 0 0 0 0.2rem rgba(var(--vz-danger-rgb, 220, 53, 69), 0.35);
            animation: combined-reminder-bell-pulse 1.8s ease-in-out infinite;
        }

        .combined-reminder-bell--active .combined-reminder-bell-icon {
            animation: combined-reminder-bell-wobble 1.1s ease-in-out infinite;
            transform-origin: top center;
        }

        @keyframes combined-reminder-bell-pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0.2rem rgba(var(--vz-danger-rgb, 220, 53, 69), 0.35);
            }

            50% {
                box-shadow: 0 0 0 0.35rem rgba(var(--vz-danger-rgb, 220, 53, 69), 0.2);
            }
        }

        @keyframes combined-reminder-bell-wobble {

            0%,
            100% {
                transform: rotate(0);
            }

            15% {
                transform: rotate(11deg);
            }

            30% {
                transform: rotate(-9deg);
            }

            45% {
                transform: rotate(6deg);
            }

            60% {
                transform: rotate(-3deg);
            }
        }

        .combined-reminder-mark-read-btn {
            background-color: #fff !important;
            border: 1px solid rgba(255, 255, 255, 0.85) !important;
        }

        .combined-reminder-mark-read-btn:hover {
            background-color: #f8f9fa !important;
            color: var(--vz-primary, #0d6efd) !important;
        }

        .combined-reminder-row--read {
            opacity: 0.85;
        }

        .combined-reminder-row {
            cursor: pointer;
        }

        .combined-reminder-tabs .nav-link {
            font-size: 0.8125rem;
            font-weight: 600;
            padding: 0.5rem 0.35rem;
            border: none;
            border-radius: 0;
            color: var(--vz-secondary-color, #878a99);
            background: transparent;
        }

        .combined-reminder-tabs .nav-link.active {
            color: var(--vz-primary, #405189);
            border-bottom: 2px solid var(--vz-primary, #405189);
            background: transparent;
        }

        .combined-reminder-tabs .nav-link:focus-visible {
            box-shadow: none;
        }

        .combined-reminder-empty {
            min-height: 140px;
        }
    </style>
</div>
