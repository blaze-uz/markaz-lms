@extends('layouts.master')
@section('title')
@lang("translation.students.trial_lesson_notice")
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<form action="{{ route('admin.groups.trial-lesson-notice', $item) }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="alert alert-info">
                <h4 class="alert-heading">@lang("translation.students.trial_lesson_notice_content") 📝</h4>
                <p>{{ $template->content }}</p>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="card card-height-100">
                <div class="card-body">
                    <div>
                        <label for="date">
                            @lang("translation.students.trial_lesson_date")
                        </label>
                        <input type="datetime-local" id="date" name="date" class="form-control" value="{{now()}}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang("translation.students.count"): {{ count($students) }}</h3>
        </div>
        <div class="card-body">
            <div class="mb-3 form-check form-switch form-switch-md" dir="ltr">
                <input type="checkbox" class="form-check-input" id="customSwitchsizelg" name="parent_sms_status"
                    aria-labelledby="send-sms-label" aria-describedby="send-sms-description">
                <label class="form-check-label" for="customSwitchsizelg" id="send-sms-label">
                    @lang("translation.notice_also_parents")
                </label>
                <div class="text-sm info text-muted">
                    @lang('translation.notice_also_parents_description')
                </div>
            </div>

            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>@lang("translation.students.full_name")</th>
                    <th class="text-center">@lang("translation.students.trial_lesson_notice")</th>
                </tr>
                @foreach($students as $student)
                <tr>
                    <td>{{ $student->id }}</td>
                    <td>
                        <a href="{{ route('admin.students.show', $student->id) }}" class="text-reset">
                            {{ $student->getFullName() }}
                        </a>
                    </td>
                    <td class="text-center">
                        <label>
                            <input class="form-check-input" type="checkbox" name="students[]" value="{{ $student->id }}"
                                checked />
                        </label>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
        <div class="card-footer">
            {{-- <div class="d-flex flex-column gap-3"> --}}
                <button class="btn btn-primary" type="submit">
                    <div class="d-flex align-items-center gap-1">
                        <i class="ri-mail-send-line"></i>
                        @lang("translation.students.send")
                    </div>
                </button>
                {{--
            </div> --}}
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</form>
@endsection
@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
            updateDateTime();

            function getUzbekMonthName(monthNumber) {
                const uzbekMonths = [
                    "Yanvar", "Fevral", "Mart", "Aprel", "May", "Iyun",
                    "Iyul", "Avgust", "Sentabr", "Oktabr", "Noyabr", "Dekabr"
                ];
                return uzbekMonths[monthNumber];
            }

            function updateDateTime() {
                const dateInput = document.getElementById('date').value;
                const selectedDate = new Date(dateInput);
                const day = selectedDate.getDate().toString();
                const monthNumber = selectedDate.getMonth();
                const month = getUzbekMonthName(monthNumber);
                const hours = selectedDate.getHours().toString().padStart(2, '0');
                const minutes = selectedDate.getMinutes().toString().padStart(2, '0');
                document.getElementById('day').innerText = day;
                document.getElementById('month').innerText = month;
                document.getElementById('time').innerText = `${hours}:${minutes}`;
            }

            document.getElementById('date').addEventListener('change', updateDateTime);
        })
</script>
@endsection
