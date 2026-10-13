<div>
    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush
    @php
        // Check if user has limited permission (only name-only permission and no other group update permissions)
        $hasNameOnlyPermission =
            auth()->user()->hasPermissionTo('group-update-name-only') && !auth()->user()->hasRole('Admin');
        $hasOtherGroupPermissions = auth()
            ->user()
            ->hasAnyPermission([
                'group-update',
                'group-update-teacher-percentage',
                'group-update-debt',
                'group-update-group-attach-settings',
            ]);

        // User can only update name if they have name-only permission AND don't have other group permissions
        $canOnlyUpdateName = $hasNameOnlyPermission && !$hasOtherGroupPermissions;
    @endphp

    <!-- Add information alert for users with limited permissions -->
    @if ($canOnlyUpdateName)
        <div class="alert alert-info mb-3">
            <i class="ri-information-line me-2"></i>
            @lang('translation.limited_permission_note', ['field' => __('translation.groups.name')])
        </div>
    @endif

    <div class="row">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="col-md-12 col-sm-12">
            <div class="mb-3 card card-height-100 rounded-4">
                <div class="card-header rounded-top-4">
                    @lang('translation.groups.settings')
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <x-input :label="__('translation.groups.name')" name="name" class="firstCapitalInput" :placeholder="__('translation.groups.name')"
                                :value="$item->name ?? (old('name') ?? '')" />
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <x-money-input :label="__('translation.groups.group_amount')" name="price" :placeholder="__('translation.groups.group_amount')" :value="$item->price ?? (old('price') ?? '')"
                                :disabled="$canOnlyUpdateName" />

                            <!-- Add hidden input to preserve value when disabled -->
                            @if ($canOnlyUpdateName)
                                <input type="hidden" name="price" value="{{ $item->price ?? (old('price') ?? '') }}">
                            @endif
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="mb-3 form-group">
                                <label for="course">@lang('translation.groups.course')</label>
                                <select name="course_id" id="course"
                                    class="form-control select2course @error('course_id') is-invalid @enderror"
                                    {{ $canOnlyUpdateName ? 'disabled' : '' }}>
                                    <option value="">@lang('translation.groups.select_course')</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}"
                                            @if (isset($item) && $item->course?->id == $course->id) selected @endif>
                                            {{ $course->name }}
                                            {{-- | {{ money($course->price) }} --}}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <!-- Add hidden input to preserve value when disabled -->
                                @if ($canOnlyUpdateName && isset($item) && $item->course)
                                    <input type="hidden" name="course_id" value="{{ $item->course->id }}">
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="mb-3 form-group">
                                <label for="branch">@lang('translation.groups.choose_branch')</label>
                                <select name="branch_id" id="branch_id"
                                    class="form-select @error('branch_id') is-invalid @enderror"
                                    {{ $canOnlyUpdateName ? 'disabled' : '' }}>
                                    <option value="">@lang('translation.groups.choose_branch')</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            @if (auth()->user()->current_branch_id == $branch->id) selected @endif
                                            {{ old('branch_id', isset($item) ? $item->branches->first()->id : null) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <!-- Add hidden input to preserve value when disabled -->
                                @if ($canOnlyUpdateName && isset($item) && $item->branches->first())
                                    <input type="hidden" name="branch_id" value="{{ $item->branches->first()->id }}">
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="mb-3 form-group">
                                <label for="teacher">@lang('translation.groups.teacher')</label>
                                @role('Teacher')
                                    <input type="hidden" name="teacher_id" value="{{ auth()->id() }}">
                                @endrole
                                <select name="teacher_id" id="teacher"
                                    class="form-control select2 @error('teacher_id') is-invalid @enderror"
                                    @role('Teacher') disabled @endrole {{ $canOnlyUpdateName ? 'disabled' : '' }}>
                                    @hasrole('Teacher')
                                        <option value="{{ auth()->user()->id }}" selected>{{ auth()->user()->name }}
                                        </option>
                                    @else
                                        <option value="">@lang('translation.groups.select_teacher')</option>
                                        @foreach ($teachers as $teacher)
                                            <option value="{{ $teacher->id }}"
                                                @if (isset($item) && $item->teacher?->id == $teacher->id) selected @endif
                                                @if ($selectedTeacherId == $teacher->id) selected @endif>
                                                {{ $teacher->name }}
                                            </option>
                                        @endforeach
                                    @endhasrole
                                </select>
                                @error('teacher_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <!-- Add hidden input to preserve value when disabled -->
                                @if ($canOnlyUpdateName && isset($item) && $item->teacher)
                                    <input type="hidden" name="teacher_id" value="{{ $item->teacher->id }}">
                                @endif
                            </div>
                        </div>
                        <a href="javascript:void(0);" id="toggle-extra-settings" class="d-block mb-3">
                            <span class="me-1">
                                <i class="fas fa-cog"></i>
                            </span>
                            {{ __('translation.groups.extra_settings') }}
                        </a>

                        <div class="col-md-6 col-sm-12" id="extra-settings-div" style="display: none">
                            @can('group-update-teacher-percentage')
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        name="teacher_percentage_switch" @if (isset($item) && $item->teacher_percentage) checked @endif
                                        id="teacherPercentageSwitch" {{ $canOnlyUpdateName ? 'disabled' : '' }}>
                                    <label class="form-check-label" for="teacherPercentageSwitch">
                                        @lang('translation.groups.change_teacher_percentage')
                                    </label>
                                </div>
                                <div id="teacherPercentage" @class([
                                    'd-none' => !isset($item) || !$item->teacher_percentage,
                                ])>
                                    <x-input :label="__('translation.groups.teacher_percentage')" type="number" name="teacher_percentage" :value="$item->teacher_percentage ?? (old('teacher_percentage') ?? '')"
                                        :disabled="$canOnlyUpdateName" />

                                    <!-- Add hidden input to preserve value when disabled -->
                                    @if ($canOnlyUpdateName && isset($item) && $item->teacher_percentage)
                                        <input type="hidden" name="teacher_percentage"
                                            value="{{ $item->teacher_percentage }}">
                                        <input type="hidden" name="teacher_percentage_switch" value="on">
                                    @endif
                                </div>
                            @endcan
                            {{-- @if (!isset($item))
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        name="create_lead_board_switch" id="createLeadBoardSwitch"
                                        {{ $canOnlyUpdateName ? 'disabled' : '' }}>
                                    <label class="form-check-label" for="createLeadBoardSwitch">
                                        @lang('translation.groups.create_lead_board')
                                    </label>
                                </div>
                            @endif --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="col-md-4 col-sm-12">
            <div class="card card-height-100">
                <div class="card-header">
                    @lang('translation.groups.monthly_payment_settings')
                </div>
                <div class="card-body">
                    <label for="">{{ __('translation.groups.payment_type') }}</label>
                    <div class="flex-wrap gap-2 d-flex">
                        <div class="w-auto">
                            <input type="radio" class="btn-check" name="payment_type" id="danger-outlined"
                                value="date_to_date"
                                @if (isset($item) && $item->payment_type == 'date_to_date') checked
                            @else checked @endif
                                {{ $canOnlyUpdateName ? 'disabled' : '' }}>
                            <label class="btn btn-outline-primary text-nowrap w-100" for="danger-outlined">
                                <i class="ri-calendar-2-line"></i>
                                {{ __('translation.groups.date_to_date') }}
                            </label>
                        </div>
                        <div class="w-auto">
                            <input type="radio" class="btn-check" name="payment_type" id="primary-outlined"
                                value="by_count_lessons" @if (isset($item) && $item->payment_type == 'by_count_lessons') checked @endif
                                {{ $canOnlyUpdateName ? 'disabled' : '' }}>
                            <label class="btn btn-outline-primary text-nowrap w-100" for="primary-outlined">
                                <i class="ri-git-repository-line"></i>
                                {{ __('translation.groups.by_count_lessons') }}
                            </label>
                        </div>
                        @error('payment_type')
                            <div class="text-danger">* {{ $message }}</div>
                        @enderror

                        <!-- Add hidden input to preserve value when disabled -->
                        @if ($canOnlyUpdateName && isset($item))
                            <input type="hidden" name="payment_type" value="{{ $item->payment_type }}">
                        @endif
                    </div>
                    <div class="lesson_count_container">
                        <x-input :label="__('translation.groups.lesson_count')" name="lesson_count" :placeholder="__('translation.groups.lesson_count')" :value="$item->lesson_count ?? (old('lesson_count') ?? '')"
                            :disabled="$canOnlyUpdateName" />

                        <!-- Add hidden input to preserve value when disabled -->
                        @if ($canOnlyUpdateName && isset($item) && $item->lesson_count)
                            <input type="hidden" name="lesson_count" value="{{ $item->lesson_count }}">
                        @endif
                    </div>
                    <div class="start_date_container">
                        <x-input type="date" :label="__('translation.groups.payment_day')" name="start_date" :placeholder="__('translation.groups.only_day')"
                            :value="isset($item->start_date)
                                ? $item->start_date->format('Y-m-d')
                                : old('start_date', now()->startOfMonth()->format('Y-m-d'))" :disabled="$canOnlyUpdateName" />

                        <!-- Add hidden input to preserve value when disabled -->
                        @if ($canOnlyUpdateName && isset($item) && $item->start_date)
                            <input type="hidden" name="start_date"
                                value="{{ $item->start_date->format('Y-m-d') }}">
                        @endif

                        <div class="alert alert-warning">
                            @lang('translation.groups.payment_day_warning')
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
    <livewire:group.group-time-form :group_id="$item->id ?? null" :readonly="$canOnlyUpdateName" />
    @push('custom-scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toggleBtn = document.getElementById('toggle-extra-settings');
                const extraDiv = document.getElementById('extra-settings-div');

                if (toggleBtn && extraDiv) {
                    // Check if the div has any visible content
                    const hasContent = extraDiv.children.length > 0 &&
                        Array.from(extraDiv.children).some(child =>
                            child.style.display !== 'none' &&
                            !child.classList.contains('d-none') &&
                            child.textContent.trim() !== ''
                        );

                    // Hide the toggle button if there's no content
                    if (!hasContent) {
                        toggleBtn.style.display = 'none';
                        return;
                    }

                    toggleBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (extraDiv.style.display === 'none') {
                            extraDiv.style.display = 'block';
                            extraDiv.style.visibility = 'visible';
                            extraDiv.style.height = 'auto';

                            // Initialize teacher percentage toggle when extra settings are shown
                            setTimeout(function() {
                                initializeTeacherPercentageToggle();
                            }, 100);
                        } else {
                            extraDiv.style.display = 'none';
                            extraDiv.style.visibility = 'hidden';
                            extraDiv.style.height = '0';
                        }
                    });
                }
            });

            // function toggleContainers() {
            //     const paymentType = document.querySelector('input[name="payment_type"]:checked')?.value;
            //     const lessonCountContainer = document.querySelector('.lesson_count_container');
            //     const paymentDateContainer = document.querySelector('.start_date_container');

            //     if (paymentType === 'by_count_lessons') {
            //         lessonCountContainer.style.display = 'block';
            //         paymentDateContainer.style.display = 'none';
            //     } else {
            //         lessonCountContainer.style.display = 'none';
            //         paymentDateContainer.style.display = 'block';
            //     }
            // }

            // // Initial call to set the initial state based on the default selected radio button
            // toggleContainers();

            // Add an event listener to the radio buttons
            // const radioButtons = document.querySelectorAll('input[name="payment_type"]');
            // radioButtons.forEach(function(radioButton) {
            //     radioButton.addEventListener('change', toggleContainers);
            // });

            // Teacher percentage toggle functionality
            function initializeTeacherPercentageToggle() {
                const teacherPercentageSwitch = $('#teacherPercentageSwitch');
                const teacherPercentageDiv = $('#teacherPercentage');

                if (teacherPercentageSwitch.length && teacherPercentageDiv.length) {
                    // Set initial state based on checkbox
                    if (teacherPercentageSwitch.is(':checked')) {
                        teacherPercentageDiv.removeClass('d-none').show();
                    } else {
                        teacherPercentageDiv.addClass('d-none').hide();
                    }

                    // Remove any existing event listeners to prevent duplicates
                    teacherPercentageSwitch.off('change.teacherPercentage');

                    // Add the toggle functionality
                    teacherPercentageSwitch.on('change.teacherPercentage', function() {
                        if ($(this).is(':checked')) {
                            teacherPercentageDiv.removeClass('d-none').show();
                        } else {
                            teacherPercentageDiv.addClass('d-none').hide();
                        }
                    });
                }
            }

            // Initialize teacher percentage toggle on page load
            $(document).ready(function() {
                initializeTeacherPercentageToggle();
            });

            $(document).ready(function() {
                $('.select2').select2({
                    placeholder: "@lang('translation.groups.choose_teacher')",
                    allowClear: true,
                });
                $('.select2course').select2({
                    placeholder: "@lang('translation.groups.choose_course')",
                    allowClear: true,
                });
            });
        </script>
    @endpush
</div>
