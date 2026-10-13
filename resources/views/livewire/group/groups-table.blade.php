<div>
    @push('css')
        <link rel="stylesheet" href="{{ asset('assets/libs/tom-select/tom-select.min.css') }}" />
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Modern Groups Cards - Matching Dashboard Style */
            .row .col-6 .status-card,
            .row .col-md-6 .status-card,
            .row .col-xl-3 .status-card {
                border: none !important;
                border-radius: 20px !important;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
                background: #ffffff !important;
                position: relative !important;
                overflow: hidden !important;
                cursor: pointer !important;
                min-height: 80px !important;
                border: 1px solid rgba(255, 255, 255, 0.2) !important;
            }

            .row .col-6 .status-card::before,
            .row .col-md-6 .status-card::before,
            .row .col-xl-3 .status-card::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(168, 85, 247, 0.05) 100%) !important;
                opacity: 0 !important;
                transition: opacity 0.4s ease !important;
            }

            .row .col-6 .status-card:hover,
            .row .col-md-6 .status-card:hover,
            .row .col-xl-3 .status-card:hover {
                transform: translateY(-8px) !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
                border-color: rgba(99, 102, 241, 0.2) !important;
            }

            .row .col-6 .status-card:hover::before,
            .row .col-md-6 .status-card:hover::before,
            .row .col-xl-3 .status-card:hover::before {
                opacity: 1 !important;
            }

            /* Enhanced Selected/Active State */
            .row .col-6 .status-card.selected,
            .row .col-md-6 .status-card.selected,
            .row .col-xl-3 .status-card.selected {
                box-shadow: 0 16px 32px rgba(0, 0, 0, 0.15) !important;
                border: 2px solid rgba(99, 102, 241, 0.3) !important;
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(168, 85, 247, 0.08) 100%) !important;
            }

            .row .col-6 .status-card.selected::before,
            .row .col-md-6 .status-card.selected::before,
            .row .col-xl-3 .status-card.selected::before {
                opacity: 0.8 !important;
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%) !important;
            }

            /* Modern Status Icons */
            .row .col-6 .status-icon,
            .row .col-md-6 .status-icon,
            .row .col-xl-3 .status-icon {
                width: 48px !important;
                height: 48px !important;
                border-radius: 16px !important;
                background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                color: white !important;
                font-size: 20px !important;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3) !important;
            }

            .row .col-6 .status-card:hover .status-icon,
            .row .col-md-6 .status-card:hover .status-icon,
            .row .col-xl-3 .status-card:hover .status-icon {
                transform: scale(1.15) rotate(5deg) !important;
                box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4) !important;
            }

            /* Enhanced text styling */
            .row .col-6 .status-card .card-body p,
            .row .col-md-6 .status-card .card-body p,
            .row .col-xl-3 .status-card .card-body p {
                color: #64748b !important;
                font-weight: 500 !important;
                letter-spacing: 0.025em !important;
            }

            .row .col-6 .status-card .card-body h4,
            .row .col-md-6 .status-card .card-body h4,
            .row .col-xl-3 .status-card .card-body h4 {
                color: #1e293b !important;
                font-weight: 700 !important;
                letter-spacing: -0.025em !important;
            }

            /* Distinct Color Themes for Each Status Card */
            /* All Groups - Blue Theme */
            .row .col-6:nth-child(1) .status-icon,
            .row .col-md-6:nth-child(1) .status-icon,
            .row .col-xl-3:nth-child(1) .status-icon {
                background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3) !important;
            }

            .row .col-6:nth-child(1) .status-card:hover .status-icon,
            .row .col-md-6:nth-child(1) .status-card:hover .status-icon,
            .row .col-xl-3:nth-child(1) .status-card:hover .status-icon {
                box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4) !important;
            }

            .row .col-6:nth-child(1) .status-card.selected,
            .row .col-md-6:nth-child(1) .status-card.selected,
            .row .col-xl-3:nth-child(1) .status-card.selected {
                border-color: rgba(59, 130, 246, 0.4) !important;
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(29, 78, 216, 0.08) 100%) !important;
            }

            /* Active Groups - Green Theme */
            .row .col-6:nth-child(2) .status-icon,
            .row .col-md-6:nth-child(2) .status-icon,
            .row .col-xl-3:nth-child(2) .status-icon {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
                box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
            }

            .row .col-6:nth-child(2) .status-card:hover .status-icon,
            .row .col-md-6:nth-child(2) .status-card:hover .status-icon,
            .row .col-xl-3:nth-child(2) .status-card:hover .status-icon {
                box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4) !important;
            }

            .row .col-6:nth-child(2) .status-card.selected,
            .row .col-md-6:nth-child(2) .status-card.selected,
            .row .col-xl-3:nth-child(2) .status-card.selected {
                border-color: rgba(16, 185, 129, 0.4) !important;
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.08) 100%) !important;
            }

            /* Demo Groups - Orange Theme */
            .row .col-6:nth-child(3) .status-icon,
            .row .col-md-6:nth-child(3) .status-icon,
            .row .col-xl-3:nth-child(3) .status-icon {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
                box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3) !important;
            }

            .row .col-6:nth-child(3) .status-card:hover .status-icon,
            .row .col-md-6:nth-child(3) .status-card:hover .status-icon,
            .row .col-xl-3:nth-child(3) .status-card:hover .status-icon {
                box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4) !important;
            }

            .row .col-6:nth-child(3) .status-card.selected,
            .row .col-md-6:nth-child(3) .status-card.selected,
            .row .col-xl-3:nth-child(3) .status-card.selected {
                border-color: rgba(245, 158, 11, 0.4) !important;
                background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(217, 119, 6, 0.08) 100%) !important;
            }

            /* Completed Groups - Red Theme */
            .row .col-6:nth-child(4) .status-icon,
            .row .col-md-6:nth-child(4) .status-icon,
            .row .col-xl-3:nth-child(4) .status-icon {
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
            }

            .row .col-6:nth-child(4) .status-card:hover .status-icon,
            .row .col-md-6:nth-child(4) .status-card:hover .status-icon,
            .row .col-xl-3:nth-child(4) .status-card:hover .status-icon {
                box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4) !important;
            }

            .row .col-6:nth-child(4) .status-card.selected,
            .row .col-md-6:nth-child(4) .status-card.selected,
            .row .col-xl-3:nth-child(4) .status-card.selected {
                border-color: rgba(239, 68, 68, 0.4) !important;
                background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(220, 38, 38, 0.08) 100%) !important;
            }

            /* Mobile Responsive Styling */
            @media (max-width: 767.98px) {

                .row .col-6 .status-card,
                .row .col-md-6 .status-card,
                .row .col-xl-3 .status-card {
                    border-radius: 16px !important;
                    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08) !important;
                    min-height: 70px !important;
                }

                .row .col-6 .status-card:hover,
                .row .col-md-6 .status-card:hover,
                .row .col-xl-3 .status-card:hover {
                    transform: translateY(-4px) !important;
                    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
                }

                .row .col-6 .status-icon,
                .row .col-md-6 .status-icon,
                .row .col-xl-3 .status-icon {
                    width: 40px !important;
                    height: 40px !important;
                    font-size: 16px !important;
                    border-radius: 12px !important;
                }

                .row .col-6 .status-card:hover .status-icon,
                .row .col-md-6 .status-card:hover .status-icon,
                .row .col-xl-3 .status-card:hover .status-icon {
                    transform: scale(1.1) rotate(3deg) !important;
                }

                .row .col-6 .status-card.selected,
                .row .col-md-6 .status-card.selected,
                .row .col-xl-3 .status-card.selected {
                    transform: translateY(-3px) !important;
                }
            }

            /* Tablet Responsive Styling */
            @media (min-width: 768px) and (max-width: 1199.98px) {

                .row .col-6 .status-card,
                .row .col-md-6 .status-card,
                .row .col-xl-3 .status-card {
                    border-radius: 18px !important;
                }

                .row .col-6 .status-card:hover,
                .row .col-md-6 .status-card:hover,
                .row .col-xl-3 .status-card:hover {
                    transform: translateY(-6px) !important;
                }

                .row .col-6 .status-icon,
                .row .col-md-6 .status-icon,
                .row .col-xl-3 .status-icon {
                    width: 44px !important;
                    height: 44px !important;
                    font-size: 18px !important;
                    border-radius: 14px !important;
                }
            }

            /* Enhanced Animations */
            @keyframes statusCardPulse {

                0%,
                100% {
                    opacity: 1;
                }

                50% {
                    opacity: 0.8;
                }
            }

            .row .col-6 .status-card.selected::before,
            .row .col-md-6 .status-card.selected::before,
            .row .col-xl-3 .status-card.selected::before {
                animation: statusCardPulse 2s infinite !important;
            }

            /* Number Animation */
            @keyframes countUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .row .col-6 .status-card .card-body h4 span,
            .row .col-md-6 .status-card .card-body h4 span,
            .row .col-xl-3 .status-card .card-body h4 span {
                display: inline-block !important;
                animation: countUp 0.6s ease-out !important;
                transition: all 0.3s ease !important;
            }

            .row .col-6 .status-card:hover .card-body h4 span,
            .row .col-md-6 .status-card:hover .card-body h4 span,
            .row .col-xl-3 .status-card:hover .card-body h4 span {
                transform: scale(1.05) !important;
            }

            /* Legacy selected state styling replaced by modern card styling above */

            /* Enhanced click animation */
            .status-card.clicked {
                animation: modernCardClick 0.2s ease-out;
            }

            @keyframes modernCardClick {
                0% {
                    transform: translateY(-2px) scale(1);
                }

                50% {
                    transform: translateY(-1px) scale(0.98);
                }

                100% {
                    transform: translateY(-2px) scale(1);
                }
            }

            /* Icon styling for selected cards */
            .status-card.selected .avatar-title {
                background: rgba(255, 255, 255, 0.2) !important;
                color: white !important;
                backdrop-filter: blur(10px);
            }

            /* Mobile optimizations */
            @media (max-width: 767.98px) {
                .status-card {
                    border-radius: 10px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                    min-height: 60px;
                }

                .status-card:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
                }

                .status-icon {
                    width: 28px;
                    height: 28px;
                    font-size: 12px;
                    border-radius: 6px;
                }

                .card-body {
                    padding: 0.5rem !important;
                }
            }

            /* Tablet optimizations */
            @media (min-width: 768px) and (max-width: 1199.98px) {
                .status-card:hover {
                    transform: translateY(-2px);
                }
            }

            .select2-selection__clear {
                position: absolute !important;
                right: 20px !important;
                top: 50% !important;
                transform: translateY(-50%) !important;
            }

            .select2-container .select2-selection--single {
                height: 38px !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 36px !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px !important;
            }

            /* Note: Button styles (.add-btn-card, .export-btn-card, etc.) are now in /public/assets/css/custom.css */
        </style>
    @endpush

    <!-- Status Cards -->
    <div class="row g-2 g-md-3 ">
        @foreach ([
        [
            'state' => null,
            'title' => 'groups.groups',
            'icon' => 'ri-group-line',
            'color' => 'success',
            'count' => $statusCounts['total'],
            'selected' => !$filterState,
        ],
        [
            'state' => 'active',
            'title' => 'groups.active_groups',
            'icon' => 'ri-check-double-line',
            'color' => 'success',
            'count' => $statusCounts['active'] ?? 0,
            'selected' => $filterState === 'active',
        ],
        [
            'state' => 'demo',
            'title' => 'groups.demo_groups',
            'icon' => 'ri-time-line',
            'color' => 'info',
            'count' => $statusCounts['demo'] ?? 0,
            'selected' => $filterState === 'demo',
        ],
        [
            'state' => 'completed',
            'title' => 'groups.completed_groups',
            'icon' => 'ri-archive-line',
            'color' => 'warning',
            'count' => $statusCounts['completed'] ?? 0,
            'selected' => $filterState === 'completed',
        ],
    ] as $card)
            <div class="col-6 col-md-6 col-xl-3">
                <div wire:click="setFilterState({{ is_null($card['state']) ? 'null' : "'" . $card['state'] . "'" }})"
                    data-state="{{ $card['state'] ?? 'all' }}"
                    class="status-card card {{ $card['selected'] ? 'selected' : '' }}">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="flex-grow-1">
                                {{-- <p class="mb-1 fw-medium text-muted fs-7 fs-md-6">@lang('translation.' . $card['title'])</p>
                                <h4 class="mb-0 fw-bold text-dark fs-5 fs-md-4">
                                    <span>{{ $card['count'] }}</span>
                                </h4> --}}

                                <h4 class="mb-2 fs-22 fw-semibold">{{ $card['count'] }}</h4>
                                <p class="mb-0 text-uppercase fw-medium fs-13 text-muted">
                                    @lang('translation.' . $card['title'])
                                </p>

                            </div>
                            <div class="flex-shrink-0">
                                <div class="status-icon">
                                    <i class="{{ $card['icon'] }}"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Main Content -->
    <div class="card position-relative overflow-hidden rounded-4">
        <!-- Filters Section -->
        <div class="card-header border-0 bg-light-subtle py-3">
            <div class="row g-3">
                <!-- Search Box -->
                <div class="col-12 col-md-12 col-lg-3">
                    <div class="search-box position-relative">
                        <input type="text" class="form-control" placeholder="@lang('translation.search')"
                            wire:model.live.debounce.1000ms="search">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                </div>
                <!-- Add Button: Mobile Only -->
                <div class="col-12 col-md-3 d-md-none d-lg-none mb-2">
                    <a href="{{ route('admin.groups.create') }}" class="add-btn-card w-100 text-center">
                        <i class="ri-add-line me-1"></i>
                        @lang('translation.groups.add')
                    </a>
                </div>
                @if (!auth()->user()->isTeacher())
                    <!-- Course Filter: Tablet full width, Desktop 1/4 -->
                    <div class="col-12 col-md-12 col-lg-3 d-none d-md-block mb-2 mb-lg-0" wire:ignore>
                        <select class="form-select select2" wire:model.live="filterCourse" id="course-select">
                            <option value="">@lang('translation.courses.choose')</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Teacher Filter: Tablet full width, Desktop 1/4 -->
                    <div class="col-12 col-md-12 col-lg-3 d-none d-md-block mb-2 mb-lg-0" wire:ignore>
                        <select class="form-select select2" wire:model.live="filterTeacher" id="teacher-select">
                            <option value="">@lang('translation.users.select_teacher')</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="col-12 col-md-12 col-lg-3 d-none d-md-block mb-2 mb-lg-0"></div>
                    <div class="col-12 col-md-12 col-lg-3 d-none d-md-block mb-2 mb-lg-0"></div>
                @endif

                <!-- Desktop Actions (Export, Reset, Add) -->
                <div class="col-12 col-md-12 col-lg-3 d-none d-lg-flex">
                    <div class="d-flex gap-2 w-100 align-items-stretch">
                        <button type="button" class="btn btn-light" wire:click="resetFilters"
                            title="@lang('translation.reset_filters')">
                            <i class="ri-filter-off-line"></i>
                        </button>
                        <div class="dropdown flex-shrink-0">
                            <button class="export-btn-card dropdown-toggle" type="button" id="exportDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-file-download-line me-1"></i>
                                @lang('translation.export')
                            </button>
                            <ul class="dropdown-menu shadow-lg border-0" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item d-flex align-items-center" href="#"
                                        wire:click="exportGroups('csv')">
                                        <i class="ri-file-text-line me-2 text-success"></i>CSV</a>
                                </li>
                                <li><a class="dropdown-item d-flex align-items-center" href="#"
                                        wire:click="exportGroups('excel')">
                                        <i class="ri-file-excel-line me-2 text-success"></i>Excel</a>
                                </li>
                            </ul>
                        </div>
                        <a href="{{ route('admin.groups.create') }}" class="add-btn-card flex-grow-1 text-center">
                            <i class="ri-add-line me-1"></i>
                            @lang('translation.groups.add')
                        </a>
                    </div>
                </div>

                <!-- Tablet Actions (Export, Reset, Add) -->
                <div class="col-12 d-none d-md-flex d-lg-none mb-2">
                    <div class="d-flex gap-2 w-100 align-items-stretch">
                        <button type="button" class="btn btn-light" wire:click="resetFilters"
                            title="@lang('translation.reset_filters')">
                            <i class="ri-filter-off-line"></i>
                        </button>
                        <div class="dropdown flex-grow-1">
                            <button class="export-btn-card dropdown-toggle w-100 text-center" type="button"
                                id="tabletExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-file-download-line me-1"></i>
                                @lang('translation.export')
                            </button>
                            <ul class="dropdown-menu shadow-lg border-0" aria-labelledby="tabletExportDropdown">
                                <li><a class="dropdown-item d-flex align-items-center" href="#"
                                        wire:click="exportGroups('csv')">
                                        <i class="ri-file-text-line me-2 text-success"></i>CSV</a>
                                </li>
                                <li><a class="dropdown-item d-flex align-items-center" href="#"
                                        wire:click="exportGroups('excel')">
                                        <i class="ri-file-excel-line me-2 text-success"></i>Excel</a>
                                </li>
                            </ul>
                        </div>
                        <a href="{{ route('admin.groups.create') }}" class="add-btn-card flex-grow-1 text-center">
                            <i class="ri-add-line me-1"></i>
                            @lang('translation.groups.add')
                        </a>
                    </div>
                </div>

                <!-- Mobile Filters Button -->
                <div class="d-flex d-md-none w-100 mb-2">
                    <button type="button" class="btn btn-outline-primary w-100"
                        onclick="document.getElementById('mobileFiltersPanel').classList.toggle('d-none')">
                        <i class="ri-filter-3-line me-1"></i> @lang('translation.filters')
                    </button>
                </div>
                <!-- Mobile Filters Panel -->
                <div id="mobileFiltersPanel" class="d-none d-md-none mb-3">
                    <div class="card card-body p-3">
                        @if (!auth()->user()->isTeacher())
                            <div class="mb-3" wire:ignore>
                                <label for="mobile-course-select" class="form-label">@lang('translation.courses.choose')</label>
                                <select class="form-select select2" wire:model.live="filterCourse"
                                    id="mobile-course-select">
                                    <option value="">@lang('translation.courses.choose')</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3" wire:ignore>
                                <label for="mobile-teacher-select" class="form-label">@lang('translation.users.select_teacher')</label>
                                <select class="form-select select2" wire:model.live="filterTeacher"
                                    id="mobile-teacher-select">
                                    <option value="">@lang('translation.users.select_teacher')</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" wire:click="resetFilters"
                                title="@lang('translation.reset_filters')">
                                <i class="ri-filter-off-line"></i>
                            </button>
                            <div class="dropdown flex-grow-1">
                                <button class="export-btn-card dropdown-toggle w-100 text-center" type="button"
                                    id="mobileExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-file-download-line me-1"></i>
                                    @lang('translation.export')
                                </button>
                                <ul class="dropdown-menu shadow-lg border-0" aria-labelledby="mobileExportDropdown">
                                    <li><a class="dropdown-item d-flex align-items-center" href="#"
                                            wire:click="exportGroups('csv')">
                                            <i class="ri-file-text-line me-2 text-success"></i>CSV</a>
                                    </li>
                                    <li><a class="dropdown-item d-flex align-items-center" href="#"
                                            wire:click="exportGroups('excel')">
                                            <i class="ri-file-excel-line me-2 text-success"></i>Excel</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="card-body">
            {{-- Desktop Table View --}}
            <div class="table-responsive d-none d-md-block">
                @if ($items->count())
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>@lang('translation.groups.name')</th>
                                <th>@lang('translation.groups.teacher')</th>
                                <th class="text-nowrap">
                                    @lang('translation.groups.students_count')
                                </th>
                                <th>@lang('translation.groups.amount')</th>
                                <th class="text-end">@lang('translation.actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td class="text-nowrap">
                                        <div>
                                            <h5 class="my-1 fs-14">
                                                <a href="{{ route('admin.groups.show', $item->id) }}"
                                                    class="text-reset">
                                                    {{ $item->name }}
                                                </a>
                                                @if ($item->state == 'demo')
                                                    <span class="badge bg-success-subtle text-success">
                                                        @lang('translation.groups.demo_group')
                                                    </span>
                                                @elseif($item->state == 'completed')
                                                    <span class="badge bg-warning-subtle text-warning">
                                                        @lang('translation.groups.completed')
                                                    </span>
                                                @endif
                                            </h5>
                                            <span class="text-muted">
                                                @lang('translation.courses.course'):
                                                @if ($item->course)
                                                    {{ $item->course->name }}
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">
                                                        @lang('translation.courses.not_found')!
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        @if ($item->teacher)
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="flex-shrink-0">
                                                    @php
                                                        $avatar = $item->teacher->getFirstMediaUrl('avatar');
                                                    @endphp
                                                    @if (!$avatar)
                                                        {!! Avatar::create($item->teacher->name)->setTheme('grayscale-light')->setDimension(35)->setFontSize(13)->toSvg() !!}
                                                    @else
                                                        <img class="rounded-circle" src="{{ $avatar }}"
                                                            alt="{{ $item->teacher->name }}"
                                                            style="width: 35px; height: 35px; object-fit: cover;">
                                                    @endif
                                                </div>
                                                <a href="{{ route('admin.users.show', $item->teacher->id) }}"
                                                    class="text-reset">
                                                    {{ $item->teacher->name }}
                                                </a>
                                            </div>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">
                                                @lang('translation.users.teacher_not_found')
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $item->students_count == 0 || $item->state == 'completed'
                                                ? 'bg-warning-subtle text-warning'
                                                : 'bg-success-subtle text-success' }} fs-6 py-2 px-3">
                                            {{ $item->students_count }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        {{ money($item->price) }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            @if ($item->state !== 'completed')
                                                <form action="{{ route('admin.groups.attach-student.view') }}"
                                                    class="d-inline">
                                                    <input type="hidden" name="group_id"
                                                        value="{{ $item->id }}">
                                                    <button type="submit" class="btn btn-sm btn-success"
                                                        data-bs-toggle="tooltip" data-bs-title="@lang('translation.groups.attach_student')">
                                                        <i class="fa-solid fa-user-plus"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('admin.groups.edit', $item->id) }}"
                                                    class="btn btn-sm btn-info" data-bs-toggle="tooltip"
                                                    data-bs-title="@lang('translation.groups.edit')">
                                                    <i class="fa-solid fa-pencil"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.groups.show', $item->id) }}"
                                                class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                                data-bs-title="@lang('translation.groups.show')">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $items->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="avatar-lg mx-auto mb-4">
                            <div class="avatar-title bg-light text-primary rounded-circle fs-1">
                                <i class="ri-search-line"></i>
                            </div>
                        </div>
                        <h5>@lang('translation.no_results_found')</h5>
                        <p class="text-muted mb-0">@lang('translation.try_adjusting_your_search')</p>
                    </div>
                @endif
            </div>

            {{-- Mobile Card View --}}
            <div class="d-block d-md-none">
                @if ($items->count())
                    <div class="row g-3">
                        @foreach ($items as $item)
                            <div class="col-12">
                                <div class="card shadow-sm h-100 rounded-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="mb-0 fs-16">
                                                <a href="{{ route('admin.groups.show', $item->id) }}"
                                                    class="text-reset">
                                                    {{ $item->name }}
                                                </a>
                                                @if ($item->state == 'demo')
                                                    <span class="badge bg-success-subtle text-success ms-2">
                                                        @lang('translation.groups.demo_group')
                                                    </span>
                                                @elseif($item->state == 'completed')
                                                    <span class="badge bg-warning-subtle text-warning ms-2">
                                                        @lang('translation.groups.completed')
                                                    </span>
                                                @endif
                                            </h5>
                                            <span
                                                class="badge {{ $item->students_count == 0 || $item->state == 'completed'
                                                    ? 'bg-warning-subtle text-warning'
                                                    : 'bg-success-subtle text-success' }} fs-6 py-1 px-2">
                                                <i class="fa-solid fa-users me-1"></i>{{ $item->students_count }}
                                            </span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted">@lang('translation.courses.course'):</span>
                                            @if ($item->course)
                                                <span>{{ $item->course->name }}</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">
                                                    @lang('translation.courses.not_found')!
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mb-2 d-flex align-items-center">
                                            <span class="text-muted me-2">@lang('translation.groups.teacher'):</span>
                                            @if ($item->teacher)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="flex-shrink-0">
                                                        @php
                                                            $avatar = $item->teacher->getFirstMediaUrl('avatar');
                                                        @endphp
                                                        @if (!$avatar)
                                                            {!! Avatar::create($item->teacher->name)->setTheme('grayscale-light')->setDimension(30)->setFontSize(11)->toSvg() !!}
                                                        @else
                                                            <img class="rounded-circle" src="{{ $avatar }}"
                                                                alt="{{ $item->teacher->name }}"
                                                                style="width: 30px; height: 30px; object-fit: cover;">
                                                        @endif
                                                    </div>
                                                    <a href="{{ route('admin.users.show', $item->teacher->id) }}"
                                                        class="text-reset">
                                                        {{ $item->teacher->name }}
                                                    </a>
                                                </div>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">
                                                    @lang('translation.users.teacher_not_found')
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted">@lang('translation.groups.amount'):</span>
                                            <span>{{ money($item->price) }}</span>
                                        </div>
                                        <div class="d-flex gap-1 justify-content-end mt-2">
                                            @if ($item->state !== 'completed')
                                                <form action="{{ route('admin.groups.attach-student.view') }}"
                                                    class="d-inline">
                                                    <input type="hidden" name="group_id"
                                                        value="{{ $item->id }}">
                                                    <button type="submit" class="btn btn-sm btn-success"
                                                        data-bs-toggle="tooltip" data-bs-title="@lang('translation.groups.attach_student')">
                                                        <i class="fa-solid fa-user-plus"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('admin.groups.edit', $item->id) }}"
                                                    class="btn btn-sm btn-info" data-bs-toggle="tooltip"
                                                    data-bs-title="@lang('translation.groups.edit')">
                                                    <i class="fa-solid fa-pencil"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.groups.show', $item->id) }}"
                                                class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                                data-bs-title="@lang('translation.groups.show')">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        {{ $items->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="avatar-lg mx-auto mb-4">
                            <div class="avatar-title bg-light text-primary rounded-circle fs-1">
                                <i class="ri-search-line"></i>
                            </div>
                        </div>
                        <h5>@lang('translation.no_results_found')</h5>
                        <p class="text-muted mb-0">@lang('translation.try_adjusting_your_search')</p>
                    </div>
                @endif
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

    @push('script-bottom')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                // Select2 initialization
                $('#course-select').select2({
                    allowClear: true,
                    placeholder: "@lang('translation.courses.choose')",
                    width: '100%'
                }).on('change', function() {
                    @this.set('filterCourse', $(this).val());
                });

                $('#teacher-select').select2({
                    allowClear: true,
                    placeholder: "@lang('translation.users.select_teacher')",
                    width: '100%'
                }).on('change', function() {
                    @this.set('filterTeacher', $(this).val());
                });

                // Filter card click tracking
                $('.status-card').on('click', function() {
                    const $card = $(this);
                    const state = $card.data('state') || null;

                    // Add visual feedback
                    $card.addClass('clicked');
                    setTimeout(() => $card.removeClass('clicked'), 300);

                    // Send analytics event (if you have analytics)
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'filter_card_click', {
                            'filter_state': state,
                            'event_category': 'engagement',
                            'event_label': 'groups_filter'
                        });
                    }
                });

                // Listen for Livewire events
                Livewire.on('filtersReset', () => {
                    $('#course-select').val('').trigger('change');
                    $('#teacher-select').val('').trigger('change');
                });

                Livewire.on('filterCardClicked', (data) => {
                    // You can add additional tracking here
                    // For example, send to your analytics service
                });
            });
        </script>
    @endpush
</div>
