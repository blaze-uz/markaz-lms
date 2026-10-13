@extends('layouts.master')
@section('title')
@lang('translation.groups.start_lessons')
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<form action="{{ route('admin.groups.start-group', $item->id) }}" method="POST">
    @csrf
    <!-- Form -->
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="card card-height-100">
                <div class="card-header">
                    @lang("translation.groups.group_info")
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <x-input name="name" label="{{ __('translation.groups.name') }}" :value="$item->name"
                                attribute="disabled" />
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <x-money-input name="start_payment_amount"
                                label="{!! __('translation.students.set_first_month_payment_amount') !!}"
                                :value="$item->price ?? old('amount')" />
                        </div>
                    </div>
                    @if($item->boards()->count())
                    <div class="alert bg-danger-subtle text-danger w-100">
                        <i class="ri-error-warning-line"></i>
                        @lang("translation.groups.group_has_lead_board_disclaimer")
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
                <div class="card">
                <div class="card-header">
                    @lang("translation.groups.payment_settings")
                </div>
                <div class="card-body">
                    <div class="col-md-12 col-sm-12 mb-3">
                        <label for="">{{__("translation.groups.payment_type")}}</label>
                        <div class="d-flex justify-content-between gap-2 flex-wrap">
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="payment_type" id="pt-date-to-date"
                                    value="date_to_date" @if(isset($item) && $item->payment_type == 'date_to_date')
                                checked
                                @else checked @endif>
                                <label class="btn btn-outline-primary text-nowrap d-flex align-items-center gap-1 w-100"
                                    for="pt-date-to-date">
                                    <i class="ri-calendar-2-line"></i>
                                    {{__("translation.groups.date_to_date")}}
                                </label>
                            </div>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="payment_type" id="pt-by-count-lessons"
                                    value="by_count_lessons" @if(isset($item) && $item->payment_type ==
                                'by_count_lessons') checked @endif>
                                <label class="btn btn-outline-primary text-nowrap d-flex align-items-center gap-1 w-100"
                                    for="pt-by-count-lessons">
                                    <i class="ri-git-repository-line"></i>
                                    {{__("translation.groups.by_count_lessons")}}
                                </label>
                            </div>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="payment_type" id="pt-one-time"
                                    value="one_time" @if(isset($item) && $item->payment_type == 'one_time') checked @endif>
                                <label class="btn btn-outline-success text-nowrap d-flex align-items-center gap-1 w-100"
                                    for="pt-one-time">
                                    <i class="ri-coins-line"></i>
                                    {{__("translation.groups.one_time")}}
                                </label>
                            </div>
                            @error('payment_type')
                            <div class="text-danger w-100">* {{$message}}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="lesson_count_container">
                        <x-input :label="__('translation.groups.lesson_count')" name="lesson_count"
                            :placeholder="__('translation.groups.lesson_count')"
                            :value="$item->lesson_count ?? old('lesson_count') ?? ''" />
                    </div>
                    <div class="start_date_container">
                        <x-input :label="__('translation.groups.payment_day')" name="start_date" type="date"
                            :placeholder="__('translation.groups.only_day')"
                            :value="now()->startOfMonth()->format('Y-m-d')" />
                        <span class="text-warning">
                            * @lang("translation.groups.payment_day_warning")
                        </span>
                    </div>
                    <div class="duration_months_container mt-2">
                        <x-input :label="__('translation.groups.duration_months')" name="duration_months"
                            type="number" min="1" max="120"
                            :placeholder="__('translation.groups.duration_months_placeholder')"
                            :value="$item->duration_months ?? old('duration_months') ?? ''" />
                        <span class="text-muted small">
                            * @lang("translation.groups.duration_months_hint")
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            @lang("translation.students.students")
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>@lang("translation.students.full_name")</th>
                        <th>@lang("translation.groups.must_join_students")</th>
                    </tr>
                    @foreach($item->students as $student)
                    <tr>
                        <td>{{ $student->id }}</td>
                        <td>{{ $student->getFullName() }}</td>
                        <td>
                            <label>
                                <input class="form-check-input" type="checkbox" name="students[]"
                                    value="{{ $student->id }}" checked />
                            </label>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end mb-3">
        <x-button class="btn-primary" :confirmation="true">
            @lang("translation.btn.save")
        </x-button>
    </div>
</form>
@endsection
@section("script")
<script>
    function toggleContainers() {
        const paymentType = document.querySelector('input[name="payment_type"]:checked').value;
        const lessonCountContainer = document.querySelector('.lesson_count_container');
        const paymentDateContainer = document.querySelector('.start_date_container');
        const durationMonthsContainer = document.querySelector('.duration_months_container');

        lessonCountContainer.style.display = 'none';
        paymentDateContainer.style.display = 'none';
        durationMonthsContainer.style.display = 'none';

        if (paymentType === 'by_count_lessons') {
            lessonCountContainer.style.display = 'block';
        } else if (paymentType === 'one_time') {
            paymentDateContainer.style.display = 'block';
            durationMonthsContainer.style.display = 'block';
        } else {
            paymentDateContainer.style.display = 'block';
        }
    }

    toggleContainers();

    document.querySelectorAll('input[name="payment_type"]').forEach(function (radioButton) {
        radioButton.addEventListener('change', toggleContainers);
    });
</script>
@endsection
