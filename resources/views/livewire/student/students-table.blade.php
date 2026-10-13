<div>
    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Modern Students Cards - Matching Dashboard Style */
            .row .col-md-3 .status-card,
            .row .col-xl-3 .status-card,
            .row .col-sm-12 .status-card,
            .row .col-md-6 .status-card {
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

            .row .col-md-3 .status-card::before,
            .row .col-xl-3 .status-card::before,
            .row .col-sm-12 .status-card::before,
            .row .col-md-6 .status-card::before {
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

            .row .col-md-3 .status-card:hover,
            .row .col-xl-3 .status-card:hover,
            .row .col-sm-12 .status-card:hover,
            .row .col-md-6 .status-card:hover {
                transform: translateY(-8px) !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
                border-color: rgba(99, 102, 241, 0.2) !important;
            }

            .row .col-md-3 .status-card:hover::before,
            .row .col-xl-3 .status-card:hover::before,
            .row .col-sm-12 .status-card:hover::before,
            .row .col-md-6 .status-card:hover::before {
                opacity: 1 !important;
            }

            /* Enhanced Selected/Active State */
            .row .col-md-3 .status-card.selected,
            .row .col-xl-3 .status-card.selected,
            .row .col-sm-12 .status-card.selected,
            .row .col-md-6 .status-card.selected {
                box-shadow: 0 16px 32px rgba(0, 0, 0, 0.15) !important;
                border: 2px solid rgba(99, 102, 241, 0.3) !important;
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(168, 85, 247, 0.08) 100%) !important;
            }

            .row .col-md-3 .status-card.selected::before,
            .row .col-xl-3 .status-card.selected::before,
            .row .col-sm-12 .status-card.selected::before,
            .row .col-md-6 .status-card.selected::before {
                opacity: 0.8 !important;
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%) !important;
            }

            /* Modern Status Icons */
            .row .col-md-3 .avatar-title,
            .row .col-xl-3 .avatar-title,
            .row .col-sm-12 .avatar-title,
            .row .col-md-6 .avatar-title {
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

            .row .col-md-3 .status-card:hover .avatar-title,
            .row .col-xl-3 .status-card:hover .avatar-title,
            .row .col-sm-12 .status-card:hover .avatar-title,
            .row .col-md-6 .status-card:hover .avatar-title {
                transform: scale(1.15) rotate(5deg) !important;
                box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4) !important;
            }

            /* Enhanced text styling */
            .row .col-md-3 .status-card .card-body p,
            .row .col-xl-3 .status-card .card-body p,
            .row .col-sm-12 .status-card .card-body p,
            .row .col-md-6 .status-card .card-body p {
                color: #64748b !important;
                font-weight: 500 !important;
                letter-spacing: 0.025em !important;
            }

            .row .col-md-3 .status-card .card-body h4,
            .row .col-xl-3 .status-card .card-body h4,
            .row .col-sm-12 .status-card .card-body h4,
            .row .col-md-6 .status-card .card-body h4 {
                color: #1e293b !important;
                font-weight: 700 !important;
                letter-spacing: -0.025em !important;
            }

            /* Distinct Color Themes for Each Status Card */
            /* All Students - Blue Theme */
            .row .col-md-3:nth-child(1) .avatar-title,
            .row .col-xl-3:nth-child(1) .avatar-title {
                background: linear-gradient(135deg, #3b82f6 0%, #3b82f6 100%) !important;
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3) !important;
            }

            .row .col-md-3:nth-child(1) .status-card:hover .avatar-title,
            .row .col-xl-3:nth-child(1) .status-card:hover .avatar-title {
                box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4) !important;
            }

            .row .col-md-3:nth-child(1) .status-card.selected,
            .row .col-xl-3:nth-child(1) .status-card.selected {
                border-color: rgba(59, 130, 246, 0.4) !important;
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(29, 78, 216, 0.08) 100%) !important;
            }

            /* Active Students - Green Theme */
            .row .col-xl-3:nth-child(2) .avatar-title {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
                box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
            }

            .row .col-xl-3:nth-child(2) .status-card:hover .avatar-title {
                box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4) !important;
            }

            .row .col-xl-3:nth-child(2) .status-card.selected {
                border-color: rgba(16, 185, 129, 0.4) !important;
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.08) 100%) !important;
            }

            /* Unattached Students - Orange Theme */
            .row .col-xl-3:nth-child(3) .avatar-title {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
                box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3) !important;
            }

            .row .col-xl-3:nth-child(3) .status-card:hover .avatar-title {
                box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4) !important;
            }

            .row .col-xl-3:nth-child(3) .status-card.selected {
                border-color: rgba(245, 158, 11, 0.4) !important;
                background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(217, 119, 6, 0.08) 100%) !important;
            }

            /* Debt Students - Red Theme */
            .row .col-xl-3:nth-child(4) .avatar-title {
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
            }

            .row .col-xl-3:nth-child(4) .status-card:hover .avatar-title {
                box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4) !important;
            }

            .row .col-xl-3:nth-child(4) .status-card.selected {
                border-color: rgba(239, 68, 68, 0.4) !important;
                background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(220, 38, 38, 0.08) 100%) !important;
            }

            /* Mobile Responsive Styling */
            @media (max-width: 767.98px) {

                .row .col-md-3 .status-card,
                .row .col-xl-3 .status-card,
                .row .col-sm-12 .status-card,
                .row .col-md-6 .status-card {
                    border-radius: 16px !important;
                    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08) !important;
                    min-height: 70px !important;
                }

                .row .col-md-3 .status-card:hover,
                .row .col-xl-3 .status-card:hover,
                .row .col-sm-12 .status-card:hover,
                .row .col-md-6 .status-card:hover {
                    transform: translateY(-4px) !important;
                    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
                }

                .row .col-md-3 .avatar-title,
                .row .col-xl-3 .avatar-title,
                .row .col-sm-12 .avatar-title,
                .row .col-md-6 .avatar-title {
                    width: 40px !important;
                    height: 40px !important;
                    font-size: 16px !important;
                    border-radius: 12px !important;
                }

                .row .col-md-3 .status-card:hover .avatar-title,
                .row .col-xl-3 .status-card:hover .avatar-title,
                .row .col-sm-12 .status-card:hover .avatar-title,
                .row .col-md-6 .status-card:hover .avatar-title {
                    transform: scale(1.1) rotate(3deg) !important;
                }

                .row .col-md-3 .status-card.selected,
                .row .col-xl-3 .status-card.selected,
                .row .col-sm-12 .status-card.selected,
                .row .col-md-6 .status-card.selected {
                    transform: translateY(-3px) !important;
                }
            }

            /* Tablet Responsive Styling */
            @media (min-width: 768px) and (max-width: 1199.98px) {

                .row .col-md-3 .status-card,
                .row .col-xl-3 .status-card,
                .row .col-sm-12 .status-card,
                .row .col-md-6 .status-card {
                    border-radius: 18px !important;
                }

                .row .col-md-3 .status-card:hover,
                .row .col-xl-3 .status-card:hover,
                .row .col-sm-12 .status-card:hover,
                .row .col-md-6 .status-card:hover {
                    transform: translateY(-6px) !important;
                }

                .row .col-md-3 .avatar-title,
                .row .col-xl-3 .avatar-title,
                .row .col-sm-12 .avatar-title,
                .row .col-md-6 .avatar-title {
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

            .row .col-md-3 .status-card.selected::before,
            .row .col-xl-3 .status-card.selected::before,
            .row .col-sm-12 .status-card.selected::before,
            .row .col-md-6 .status-card.selected::before {
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

            .row .col-md-3 .status-card .card-body h4,
            .row .col-xl-3 .status-card .card-body h4,
            .row .col-sm-12 .status-card .card-body h4,
            .row .col-md-6 .status-card .card-body h4 {
                display: inline-block !important;
                animation: countUp 0.6s ease-out !important;
                transition: all 0.3s ease !important;
            }

            .row .col-md-3 .status-card:hover .card-body h4,
            .row .col-xl-3 .status-card:hover .card-body h4,
            .row .col-sm-12 .status-card:hover .card-body h4,
            .row .col-md-6 .status-card:hover .card-body h4 {
                transform: scale(1.05) !important;
            }

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

            .filter-row {
                margin-top: 1rem;
                transition: all 0.3s ease;
                max-height: 0;
                overflow: hidden;
                opacity: 0;
            }

            .filter-row.show {
                max-height: 500px;
                opacity: 1;
                margin-top: 1rem;
            }

            .filter-badge {
                position: absolute;
                top: -8px;
                right: -8px;
                font-size: 10px;
                padding: 4px 6px;
            }

            .offcanvas {
                transition: transform 0.3s ease-in-out;
            }

            .filter-section {
                padding: 1rem;
                border-bottom: 1px solid #eee;
            }

            .filter-section:last-child {
                border-bottom: none;
            }

            .filter-title {
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--vz-gray-700);
                margin-bottom: 0.5rem;
            }

            /* Loading states */
            .loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            }

            .loading-spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid var(--vz-primary);
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }

            .loading {
                opacity: 0.6;
                pointer-events: none;
            }

            /* Select2 Custom Styles */
            .select2-container--default .select2-selection--single {
                height: 38px;
                border: 1px solid var(--vz-input-border);
                background-color: var(--vz-input-bg);
                border-radius: 4px;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 36px;
                padding-left: 12px;
                color: var(--vz-body-color);
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px;
            }

            .select2-dropdown {
                border-color: var(--vz-input-border);
                background-color: var(--vz-input-bg);
            }

            .select2-search--dropdown .select2-search__field {
                padding: 6px;
                border-color: var(--vz-input-border);
                background-color: var(--vz-input-bg);
                color: var(--vz-body-color);
            }

            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: var(--vz-primary);
            }

            /* Select All Banner Styles */
            .select-all-banner {
                border-left: 4px solid;
                animation: slideDown 0.3s ease-out;
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .select-all-banner .alert-link {
                transition: all 0.2s ease;
            }

            .select-all-banner .alert-link:hover {
                transform: translateX(3px);
            }

            /* Floating Selection Actions Panel */
            .selection-actions-modal {
                position: fixed;
                bottom: 24px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 1040;
                animation: panelSlideUp 0.3s ease-out;
                pointer-events: auto;
            }

            .selection-actions-modal-content {
                background: #ffffff;
                border-radius: 24px;
                padding: 10px 16px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
                position: relative;
                display: flex;
                align-items: center;
                gap: 16px;
                max-width: 90vw;
                min-width: 400px;
                border: none;
            }

            .selection-info-section {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 14px;
                background: #f5f5f5;
                border-radius: 16px;
                border: 1px solid #e5e5e5;
            }

            .selection-count {
                color: #2d2d2d;
                font-size: 14px;
                font-weight: 500;
                white-space: nowrap;
                line-height: 1.2;
            }

            .selection-count .count-number {
                font-weight: 700;
                color: #1a1a1a;
            }

            .clear-selection-btn {
                width: 18px;
                height: 18px;
                background: transparent;
                border: none;
                color: #6b6b6b;
                font-size: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
                flex-shrink: 0;
                cursor: pointer;
                padding: 0;
                margin-left: 6px;
            }

            .clear-selection-btn:hover {
                color: #2d2d2d;
                transform: scale(1.1);
            }

            .selection-divider {
                width: 1px;
                height: 28px;
                background: #d1d1d1;
                flex-shrink: 0;
            }

            .action-buttons-group {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .select-all-btn {
                background: #f5f5f5;
                border: 1px solid #e5e5e5;
                border-radius: 8px;
                color: #2d2d2d;
                font-size: 13px;
                font-weight: 500;
                padding: 8px 14px;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: all 0.2s ease;
                white-space: nowrap;
                line-height: 1.2;
            }

            .select-all-btn:hover {
                background: #eeeeee;
                border-color: #d1d1d1;
                transform: translateY(-1px);
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .action-btn {
                background: #f5f5f5;
                border: 1px solid #e5e5e5;
                border-radius: 8px;
                padding: 8px 14px;
                font-weight: 500;
                font-size: 13px;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: all 0.2s ease;
                white-space: nowrap;
                line-height: 1.2;
                color: #2d2d2d;
            }

            .action-btn:hover {
                background: #eeeeee;
                border-color: #d1d1d1;
                transform: translateY(-1px);
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .action-btn:active {
                transform: translateY(0);
            }

            .action-btn i {
                font-size: 16px;
            }

            .action-btn.btn-info {
                color: #2d2d2d;
            }

            .action-btn.btn-info:hover {
                color: #1a1a1a;
            }

            .action-btn.btn-primary {
                color: #2d2d2d;
            }

            .action-btn.btn-primary:hover {
                color: #1a1a1a;
            }

            .action-btn.btn-danger {
                background: #e53e3e;
                color: #ffffff;
                border-color: #e53e3e;
            }

            .action-btn.btn-danger:hover {
                background: #c53030;
                border-color: #c53030;
                color: #ffffff;
            }

            .action-btn:disabled {
                opacity: 0.5;
                transform: none;
                cursor: not-allowed;
            }

            /* Dropdown Styles */
            .action-buttons-group .dropdown {
                position: relative;
            }

            .action-btn.dropdown-toggle::after {
                margin-left: 6px;
                vertical-align: middle;
            }

            .action-buttons-group .dropdown-menu {
                background: #ffffff;
                border: 1px solid #e5e5e5;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                padding: 4px;
                margin-top: 4px;
                min-width: 160px;
            }

            .action-buttons-group .dropdown-item {
                padding: 8px 12px;
                border-radius: 6px;
                color: #2d2d2d;
                font-size: 13px;
                font-weight: 500;
                display: flex;
                align-items: center;
                transition: all 0.2s ease;
            }

            .action-buttons-group .dropdown-item:hover {
                background: #f5f5f5;
                color: #1a1a1a;
            }

            .action-buttons-group .dropdown-item i {
                font-size: 16px;
                width: 20px;
            }

            .action-buttons-group .dropdown-item:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            /* Panel Animations */
            @keyframes panelSlideUp {
                from {
                    opacity: 0;
                    transform: translateX(-50%) translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0);
                }
            }

            /* Mobile Responsive */
            @media (max-width: 767.98px) {
                .selection-actions-modal {
                    bottom: 16px;
                    left: 8px;
                    right: 8px;
                    transform: none;
                    animation: panelSlideUpMobile 0.3s ease-out;
                }

                @keyframes panelSlideUpMobile {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .selection-actions-modal-content {
                    padding: 8px 12px;
                    border-radius: 16px;
                    min-width: auto;
                    max-width: none;
                    flex-direction: column;
                    gap: 8px;
                }

                .selection-info-section {
                    width: 100%;
                    justify-content: space-between;
                }

                .selection-divider {
                    display: none;
                }

                .action-buttons-group {
                    width: 100%;
                    flex-wrap: wrap;
                    gap: 6px;
                }

                .selection-count {
                    font-size: 13px;
                }

                .action-btn {
                    padding: 6px 10px;
                    font-size: 12px;
                    flex: 1;
                    min-width: 0;
                    justify-content: center;
                }

                .action-btn span {
                    display: none;
                }

                .select-all-btn {
                    flex: 1;
                    padding: 6px 10px;
                    font-size: 12px;
                }

                .select-all-btn span {
                    display: none;
                }
            }

            /* Tablet Responsive */
            @media (min-width: 768px) and (max-width: 1199.98px) {
                .selection-actions-modal-content {
                    padding: 8px 14px;
                }

                .action-btn {
                    font-size: 12px;
                    padding: 6px 10px;
                }
            }

        </style>
    @endpush
    @can('student-card-records')
        <div class="row">
            <div class="col-6 col-md-3 d-flex">
                <div wire:click="setFilterState('studentsCount')"
                    class="status-card card flex-fill {{ $filterState === 'studentsCount' ? 'selected' : '' }}">
                    <div class="p-3 card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-2 fs-22 fw-semibold">{{ $statusCounts['studentsCount'] }}</h4>
                                <p class="mb-0 text-uppercase fw-medium fs-13 text-muted">
                                    @lang('translation.students.count')
                                </p>
                            </div>
                            <div class="flex-shrink-0 avatar-sm">
                                <div class="rounded avatar-title bg-light text-primary fs-3">
                                    <i class="ri-group-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @unlessrole('Teacher')
                <div class="col-6 col-md-6 col-xl-3 d-flex">
                    <div wire:click="setFilterState('activeStudentsCount')"
                        class="status-card card flex-fill {{ $filterState === 'activeStudentsCount' ? 'selected' : '' }}">
                        <div class="p-3 card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-2 fs-22 fw-semibold">{{ $statusCounts['activeStudentsCount'] }}</h4>
                                    <p class="mb-0 text-uppercase fw-medium fs-13 text-muted">
                                        @lang('translation.students.active')
                                    </p>
                                </div>
                                <div class="flex-shrink-0 avatar-sm">
                                    <div class="rounded avatar-title bg-light text-success fs-3">
                                        <i class="ri-user-follow-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-xl-3 d-flex">
                    <div wire:click="setFilterState('unattachedStudentsCount')"
                        class="status-card card flex-fill {{ $filterState === 'unattachedStudentsCount' ? 'selected' : '' }}">
                        <div class="p-3 card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-2 fs-22 fw-semibold">{{ $statusCounts['unattachedStudentsCount'] }}</h4>
                                    <p class="mb-0 text-uppercase fw-medium fs-13 text-muted">
                                        @lang('translation.students.unattached_students')
                                    </p>
                                </div>
                                <div class="flex-shrink-0 avatar-sm">
                                    <div class="rounded avatar-title bg-light text-warning fs-3">
                                        <i class="ri-user-settings-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endunlessrole
            <div class="col-6 col-md-6 col-xl-3 d-flex">
                <div wire:click="setFilterState('debtStudentsCount')"
                    class="status-card card flex-fill {{ $filterState === 'debtStudentsCount' ? 'selected' : '' }}">
                    <div class="p-3 card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-2 fs-22 fw-semibold">{{ $statusCounts['debtStudentsCount'] }}</h4>
                                <p class="mb-0 text-uppercase fw-medium fs-13 text-muted">@lang('translation.students.debt')
                                </p>
                            </div>
                            <div class="flex-shrink-0 avatar-sm">
                                <div class="rounded avatar-title bg-light text-danger fs-3">
                                    <i class="ri-money-dollar-circle-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    <div class="p-1 card rounded-4">
        <div class="border-0 card-header">
            <div class="row align-items-center">
                <div class="col-sm-12 col-md">
                    <div class="flex-wrap gap-2 hstack">
                        <div class="search-box flex-grow-1 flex-md-grow-0" style="max-width: 300px;">
                            <input type="search" class="form-control search-input" placeholder="@lang('translation.Search')"
                                wire:model.live.debounce.1000ms="search">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                        <button class="btn btn-light position-relative" type="button"
                            wire:click="$toggle('showFilters')">
                            <i class="align-bottom ri-filter-3-line me-1"></i> @lang('translation.Filter')
                            @php
                                $activeFilters = 0;
                                if ($group_id) {
                                    $activeFilters++;
                                }
                                if ($teacherId) {
                                    $activeFilters++;
                                }
                                if ($filterState !== 'studentsCount') {
                                    $activeFilters++;
                                }
                            @endphp
                            @if ($activeFilters > 0)
                                <span class="badge bg-primary rounded-circle filter-badge">{{ $activeFilters }}</span>
                            @endif
                        </button>
                        <div class="btn-group ">
                            <button type="button" class="btn btn-soft-info dropdown-toggle rounded-4"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="align-bottom ri-file-transfer-line me-1"></i> Import/Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" wire:click="exportStudents('csv')"><i
                                            class="ri-file-download-line me-2"></i>Export CSV</a></li>
                                <li><a class="dropdown-item" href="#" wire:click="exportStudents('excel')"><i
                                            class="ri-file-excel-line me-2"></i>Export Excel</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a href="{{ route('admin.students.import.view') }}" class="dropdown-item">
                                        <i class="ri-file-upload-line me-2"></i>{{ __('translation.students.import') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                         @if (count($selectedStudents) == 0)
                            @can('student-create')
                                <a class="add-btn-card d-flex align-items-center d-flex d-md-none"
                                    href="{{ route('admin.students.create') }}">
                                    <i class="align-bottom ri-add-line me-1"></i>
                                    @lang('translation.students.add')
                                </a>
                            @endcan
                        @endif
                    </div>
                </div>
                <div class="col-sm-12 col-md-auto mt-2 mt-md-0">
                    <div class="d-flex justify-content-end gap-2">
                        @if (count($selectedStudents) > 0)
                            <div class="d-flex align-items-center me-2">
                                <span class="badge bg-primary fs-13 px-3 py-2">
                                    {{ count($selectedStudents) }} @lang('translation.selected')
                                </span>
                            </div>
                        @endif

                        @can('student-create')
                            <a class="btn btn-success btn-sm d-flex align-items-center gap-1"
                                href="{{ route('admin.students.create') }}">
                                <i class="ri-add-line"></i>
                                <span class="d-none d-md-inline">@lang('translation.students.add')</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Inline Filters -->
            <div class="filter-row @if ($showFilters) show @endif mt-2">
                <div class="row g-2">
                    <div class="col-md-3 col-sm-12">
                        <div wire:ignore>
                            <select id="groups_select" class="form-select form-control" wire:model="group_id">
                                <option value="">@lang('translation.groups.select_group')</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @unlessrole('Teacher')
                        <div class="col-md-3 col-sm-12">
                            <div wire:ignore>
                                <select id="teachers_select" class="form-select form-control" wire:model="teacherId">
                                    <option value="">@lang('translation.users.select_teacher')</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endunlessrole
                    <div class="col-md-3 col-sm-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="showAllBranches"
                                wire:model.live="showAllBranches">
                            <label class="form-check-label fw-bold" for="showAllBranches">@lang('translation.show_all_branches')</label>
                        </div>
                    </div>
                    @if ($filterState === 'deletedStudentsCount')
                        <div class="col-md-4 col-sm-12">
                            <input type="date" class="form-control" wire:model.live="dateFrom"
                                placeholder="@lang('translation.from_date')">
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <input type="date" class="form-control" wire:model.live="dateTo"
                                placeholder="@lang('translation.to_date')">
                        </div>
                    @endif

                    <div class="col-md-3 col-sm-6">
                        <select class="form-select" wire:model.live="filterState">
                            <option value="studentsCount">{{ __('translation.students.all') }}</option>
                            <option value="activeStudentsCount">{{ __('translation.students.active') }}</option>
                            <option value="unattachedStudentsCount">{{ __('translation.students.unattached') }}
                            </option>
                            <option value="debtStudentsCount">{{ __('translation.students.debt') }}</option>
                            <option value="deletedStudentsCount">{{ __('translation.students.filter_deleted') }}
                            </option>
                        </select>
                    </div>

                                    <div class="col-md-3 col-sm-6">
                                        <select class="form-select" wire:model.live="perPage">
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="500">500</option>
                                        </select>
                                    </div>

                    <div class="col-md-2 col-sm-12">
                        <button type="button" class="w-100 btn btn-light" wire:click="clearFilters">
                            <i class="align-bottom ri-refresh-line me-1"></i>
                            {{ __('translation.reset') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Floating Selection Actions Panel -->
            @if (count($selectedStudents) > 0)
                <div class="selection-actions-modal">
                    <div class="selection-actions-modal-content">
                        <!-- Left side: Selection info with dismiss -->
                        <div class="selection-info-section">
                            <span class="selection-count">
                                <span class="count-number">{{ count($selectedStudents) }}</span> @lang('translation.selected')
                            </span>
                            <button type="button" class="clear-selection-btn" wire:click="deselectAllStudents" aria-label="Clear selection">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>

                        <!-- Vertical divider -->
                        <div class="selection-divider"></div>

                        <!-- Right side: Action buttons -->
                        <div class="action-buttons-group">
                            @if ($showSelectAllBanner)
                                <button type="button" class="select-all-btn" wire:click="selectAllStudentsAcrossPages">
                                    <i class="ri-checkbox-circle-line"></i>
                                    <span>@lang('translation.students.select_all') ({{ $items->total() }})</span>
                                </button>
                            @endif

                            @if ($filterState === 'deletedStudentsCount')
                                <button type="button" class="action-btn btn-success"
                                    wire:click="openRestoreModal"
                                    wire:loading.attr="disabled"
                                    wire:target="openRestoreModal, restoreSelectedStudents">
                                    <i class="ri-arrow-go-back-line" wire:loading.remove wire:target="restoreSelectedStudents"></i>
                                    <span class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="restoreSelectedStudents"></span>
                                    <span>@lang('translation.students.restore')</span>
                                </button>

                                @can('student-delete')
                                    <button type="button" class="action-btn btn-danger"
                                        wire:click="openForceDeleteModal"
                                        wire:loading.attr="disabled"
                                        wire:target="openForceDeleteModal, forceDeleteSelectedStudents">
                                        <i class="ri-delete-bin-line" wire:loading.remove wire:target="forceDeleteSelectedStudents"></i>
                                        <span class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="forceDeleteSelectedStudents"></span>
                                        <span>@lang('translation.students.delete_permanently')</span>
                                    </button>
                                @endcan
                            @else
                                @can('sms-create')
                                    <div class="dropdown">
                                        <button type="button" class="action-btn btn-info dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false"
                                            wire:loading.attr="disabled">
                                            <i class="ri-message-2-line"></i>
                                            <span>@lang('translation.send_message')</span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click.prevent="redirectToSendSms"
                                                    wire:loading.attr="disabled"
                                                    wire:target="redirectToSendSms">
                                                    <i class="ri-message-2-line me-2"></i>
                                                    <span>SMS</span>
                                                    <span class="spinner-border spinner-border-sm ms-2" role="status" wire:loading wire:target="redirectToSendSms"></span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click.prevent="redirectToSendTelegram"
                                                    wire:loading.attr="disabled"
                                                    wire:target="redirectToSendTelegram">
                                                    <i class="ri-telegram-line me-2"></i>
                                                    <span>Telegram</span>
                                                    <span class="spinner-border spinner-border-sm ms-2" role="status" wire:loading wire:target="redirectToSendTelegram"></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                @endcan

                                @can('student-attach-group')
                                    <button type="button" class="action-btn btn-primary"
                                        wire:click="redirectToAttachGroup"
                                        wire:loading.attr="disabled"
                                        wire:target="redirectToAttachGroup">
                                        <i class="ri-user-add-line" wire:loading.remove wire:target="redirectToAttachGroup"></i>
                                        <span class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="redirectToAttachGroup"></span>
                                        <span>@lang('translation.students.attach_to_group')</span>
                                    </button>
                                @endcan

                                @can('student-delete')
                                    <button type="button" class="action-btn btn-danger"
                                        wire:click="openDeleteModal">
                                        <i class="ri-delete-bin-line"></i>
                                        <span>@lang('translation.btn.delete')</span>
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="table-responsive table-card" wire:loading.class="opacity-50">
                @if ($items->count())
                    <table class="table align-middle" id="studentsTable">
                        <thead class="align-middle table-light">
                            <tr>
                                <th>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault"
                                            wire:model.live="selectAllCurrentPage" />
                                    </div>
                                </th>
                                <th>ID</th>
                                <th>@lang('translation.students.full_name')</th>
                                <th>@lang('translation.students.phone')</th>
                                <th>@lang('translation.students.group')</th>
                                <th>@lang('translation.branches.branch')</th>
                                @can('student-balance')
                                    <th>@lang('translation.students.balance')</th>
                                @endcan
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="list form-check-all">
                            @foreach ($items as $item)
                                <tr wire:key="student-{{ $item->id }}">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                id="student_{{ $item->id }}" wire:model.live="selectedStudents"
                                                value="{{ $item->id }}" />
                                        </div>
                                    </td>
                                    <td style="width: 25px">
                                        {{ $item->id }}
                                    </td>
                                    <td class="text-nowrap text-truncate" style="max-width: 270px">
                                        <a href="{{ route('admin.students.show', $item->id) }}"
                                            class="text-reset d-flex align-items-center">
                                            <span class="me-2">
                                                {!! Avatar::create($item->getFullName())->setTheme('grayscale-dark')->setDimension(32)->setFontSize(12)->toSvg() !!}
                                            </span>
                                            <div>
                                                {{ $item->getFullName() }}
                                                @if ($filterState === 'deletedStudentsCount' && $item->deleted_at)
                                                    <div class="text-muted">
                                                        @lang('translation.deleted_at'):
                                                        {{ $item->deleted_at->format('d.m.Y H:i') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="tel:{{ $item->phone }}" class="text-reset">
                                            {{ $item->getFormattedPhone() }}
                                        </a>
                                    </td>
                                    <td class="text-nowrap">
                                        @forelse($item->groups as $group)
                                            <a href="{{ route('admin.groups.show', $group->id) }}" class="fw-medium">
                                                {{ $group->name }}
                                            </a><br>
                                        @empty
                                            <span class="badge bg-warning-subtle text-warning">
                                                @lang('translation.students.has_no_group')
                                            </span>
                                        @endforelse
                                    </td>
                                    <td class="text-nowrap">
                                        @forelse($item->branches as $branch)
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $branch->name }}
                                            </span><br>
                                        @empty
                                            <span class="badge bg-warning-subtle text-warning">
                                                @lang('translation.no_branch')
                                            </span>
                                        @endforelse
                                    </td>
                                    @can('student-balance')
                                        <td class="text-nowrap">
                                            @if ($item->balance < 0)
                                                <span
                                                    class="border cursor-pointer badge bg-danger-subtle text-danger debt-tooltip">
                                                    {{ money($item->balance) }}
                                                </span>
                                            @else
                                                <span class="border cursor-pointer badge bg-success-subtle text-success">
                                                    {{ money($item->balance) }}
                                                </span>
                                            @endif
                                        </td>
                                    @endcan
                                    <td class="text-nowrap">
                                        @if (!$item->deleted_at)
                                            @can('student-attach-group')
                                                <form action="{{ route('admin.groups.attach-student.view') }}"
                                                    class="d-inline-block">
                                                    <input type="hidden" name="student_id" value="{{ $item->id }}">
                                                    <button type="submit" class="btn btn-success btn-sm"
                                                        data-bs-toggle="tooltip" data-bs-title="@lang('translation.groups.attach_student')">
                                                        <i class="mdi mdi-clipboard-plus-outline fs-5"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                            @can('student-payment')
                                                <a href="{{ route('admin.students.payment.view', $item->id) }}"
                                                    class="btn btn-info btn-sm" data-bs-toggle="tooltip"
                                                    data-bs-title="@lang('translation.students.payment')">
                                                    <i class="mdi mdi-cash-multiple fs-5"></i>
                                                </a>
                                            @endcan
                                            <a href="{{ route('admin.students.show', $item->id) }}"
                                                class="btn btn-info btn-sm t-show-btn">
                                                <i class="mdi mdi-eye fs-5"></i>
                                            </a>
                                            <a href="{{ route('admin.students.edit', $item->id) }}"
                                                class="btn btn-info btn-sm t-edit-btn">
                                                <i class="mdi mdi-pencil fs-5"></i>
                                            </a>
                                        @else
                                            <form action="{{ route('admin.students.restore', $item->id) }}"
                                                class="d-inline-block" method="POST">
                                                @csrf
                                                <button type="submit" data-bs-toggle="tooltip"
                                                    data-bs-trigger="hover" data-bs-placement="top"
                                                    title="@lang('translation.students.restore')"
                                                    onclick="return confirm('Are you sure?')"
                                                    class="border btn btn-ghost-dark waves-effect waves-light material-shadow-none btn-sm">
                                                    <i class="mdi mdi-delete-restore fs-4 text-muted"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.students.force-delete', $item->id) }}"
                                                class="d-inline-block" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" data-bs-toggle="tooltip"
                                                    data-bs-trigger="hover" data-bs-placement="top"
                                                    title="@lang('translation.students.delete_permanently')"
                                                    onclick="return confirm('Are you sure?')"
                                                    class="border btn btn-ghost-dark waves-effect waves-light material-shadow-none btn-sm">
                                                    <i class="mdi mdi-delete fs-4 text-muted"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <x-no-result />
                @endif
            </div>

            <div class="mt-3">
                {{ $items->links() }}
            </div>
        </div>
    </div>


    <!-- Delete confirmation modal -->
    @if ($showDeleteModal)
        <div class="modal fade show" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
            style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="deleteModalLabel">
                            @lang('translation.students.delete_confirm')
                        </h1>
                        <button type="button" class="btn-close" wire:click="closeDeleteModal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="ri-alert-line me-2"></i>
                            @lang('translation.students.delete_warning', ['count' => count($studentsToDelete)])
                        </div>

                        <div class="form-group">
                            <label for="deleteComment" class="form-label">
                                @lang('translation.students.delete_comment') <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('deleteComment') is-invalid @enderror" id="deleteComment"
                                wire:model="deleteComment" rows="3" placeholder="@lang('translation.students.delete_comment_placeholder')"></textarea>
                            @error('deleteComment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeDeleteModal">
                            @lang('translation.btn.cancel')
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteSelectedStudents"
                            wire:loading.attr="disabled" wire:target="deleteSelectedStudents">
                            <span wire:loading.remove wire:target="deleteSelectedStudents">
                                <i class="ri-delete-bin-line me-1"></i>
                                @lang('translation.btn.delete')
                            </span>
                            <span wire:loading wire:target="deleteSelectedStudents">
                                <i class="ri-loader-4-line me-1 spinner-border spinner-border-sm"></i>
                                @lang('translation.deleting')...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showRestoreModal)
        <div class="modal fade show" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel"
            style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="restoreModalLabel">
                            @lang('translation.students.restore_confirm')
                        </h1>
                        <button type="button" class="btn-close" wire:click="closeRestoreModal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ri-arrow-go-back-line me-2"></i>
                            @lang('translation.students.restore_warning', ['count' => count($studentsToRestore)])
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeRestoreModal">
                            @lang('translation.btn.cancel')
                        </button>
                        <button type="button" class="btn btn-success" wire:click="restoreSelectedStudents"
                            wire:loading.attr="disabled" wire:target="restoreSelectedStudents">
                            <span wire:loading.remove wire:target="restoreSelectedStudents">
                                <i class="ri-arrow-go-back-line me-1"></i>
                                @lang('translation.students.restore')
                            </span>
                            <span wire:loading wire:target="restoreSelectedStudents">
                                <i class="ri-loader-4-line me-1 spinner-border spinner-border-sm"></i>
                                @lang('translation.students.restoring')...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showForceDeleteModal)
        <div class="modal fade show" id="forceDeleteModal" tabindex="-1" aria-labelledby="forceDeleteModalLabel"
            style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="forceDeleteModalLabel">
                            @lang('translation.students.delete_permanently')
                        </h1>
                        <button type="button" class="btn-close" wire:click="closeForceDeleteModal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="ri-alert-line me-2"></i>
                            @lang('translation.students.force_delete_warning', ['count' => count($studentsToForceDelete)])
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeForceDeleteModal">
                            @lang('translation.btn.cancel')
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="forceDeleteSelectedStudents"
                            wire:loading.attr="disabled" wire:target="forceDeleteSelectedStudents">
                            <span wire:loading.remove wire:target="forceDeleteSelectedStudents">
                                <i class="ri-delete-bin-line me-1"></i>
                                @lang('translation.students.delete_permanently')
                            </span>
                            <span wire:loading wire:target="forceDeleteSelectedStudents">
                                <i class="ri-loader-4-line me-1 spinner-border spinner-border-sm"></i>
                                @lang('translation.deleting')...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('custom-scripts')
        <!--select2 cdn-->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#groups_select').select2({
                    placeholder: "@lang('translation.groups.select_group')",
                    width: '100%',
                });

                $('#teachers_select').select2({
                    placeholder: "@lang('translation.users.select_teacher')",
                    width: '100%',
                });

                $('#groups_select').on('change', function(e) {
                    @this.set('group_id', $(this).val());
                });

                $('#teachers_select').on('change', function(e) {
                    @this.set('teacherId', $(this).val());
                });

                // Status card click animation
                $('.status-card').on('click', function() {
                    const $card = $(this);

                    // Add visual feedback
                    $card.addClass('clicked');
                    setTimeout(() => $card.removeClass('clicked'), 300);

                    // Send analytics event (if you have analytics)
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'filter_card_click', {
                            'filter_state': $card.data('state'),
                            'event_category': 'engagement',
                            'event_label': 'students_filter'
                        });
                    }
                });
            });
        </script>
    @endpush
</div>
