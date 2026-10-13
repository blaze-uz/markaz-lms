@extends('layouts.master')
@section('title')
@lang("translation.groups.attach")
@stop
@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<livewire:group.attach-student :selectedGroupId="$selectedGroupId" :selectedStudentId="$selectedStudentId"
    :selected-students-ids="$selectedStudentsIds" />
@endsection
@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
            $('.students-select').select2({
                placeholder: "@lang('translation.groups.choose_student')",
                allowClear: true,
                multiple: true
            });
            $('.group-select').select2({
                placeholder: "@lang('translation.groups.choose_group')",
            });

            $('#attachedDateSwitch').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#attached-date-container').removeClass('d-none');
                } else {
                    $('#attached-date-container').addClass('d-none');
                    $('#attached-at').val('');
                }
            });
        });
</script>
@if($errors->any())
<script>
    console.log('errors');
            @foreach ($errors->all() as $error)
            window.notifyMessage("{!! $error !!}", 'danger')
            @endforeach
</script>
@endif
@endsection
