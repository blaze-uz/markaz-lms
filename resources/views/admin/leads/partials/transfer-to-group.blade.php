@extends('layouts.master')
@section('title')
@lang("translation.leads.board")
@endsection
@section('css')
<style>
    .form-control {
        width: auto !important;
    }
</style>
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="card">
    <div class="card-header">
        <h4 class="card-title">@lang("translation.leads.transfer_leads_instruction")</h4>
    </div>
    <div class="card-body">
        <p>Lid ma'lumotlari to'g'ri ekaniga ishonch hosil qiling. Lidlar avval o'quvchilarga aylantiriladi va
            to'plam biriktirilgan demo guruhga o'quvchi sifatida qo'shiladi! Ma'lumotlarni to'ldirish majburiy!</p>
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
<form action="{{ route('admin.boards.transfer-to-group', $item->id) }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h4 class="card-title">@lang("translation.leads.transfer_leads")</h4>
                <div>
                    @lang("translation.leads.count"): {{count($leads)}}
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>@lang('translation.students.name')</th>
                        <th>@lang('translation.students.surname')</th>
                        <th>@lang('translation.students.phone')</th>
                        <th>@lang('translation.students.gender')</th>
                    </tr>
                    @foreach($leads as $lead)
                    <tr>
                        <td>{{ $lead->id }}</td>
                        <td>
                            <label>
                                <input type="text" class="form-control" value="{{$lead['name']}}"
                                    name="leads[{{$lead->id}}][name]" required />
                            </label>
                        </td>
                        <td>
                            <label>
                                <input type="text" class="form-control" value="{{$lead['surname']}}"
                                    name="leads[{{$lead->id}}][surname]" required />
                            </label>
                        </td>
                        <td>
                            <label>
                                <input type="tel" name="leads[{{$lead->id}}][phone]" autocomplete="off"
                                    class="form-control phone @error('phone') is-invalid @enderror"
                                    value="{{$lead->phone}}" required />
                                @error('phone')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </label>
                        </td>
                        <td>
                            <div class="d-flex justify-content-start gap-2">
                                <div class="mr-3">
                                    <input type="radio" class="btn-check" name="leads[{{$lead->id}}][gender]"
                                        id="lead-male-{{$lead->id}}" value="male" required />
                                    <label class="btn btn-outline-primary" for="lead-male-{{$lead->id}}">
                                        <svg viewBox="0 0 24 24" width="1.2em" height="1.2em" class="w-5 h-4">
                                            <path fill="currentColor"
                                                d="M9 11.75A1.25 1.25 0 0 0 7.75 13A1.25 1.25 0 0 0 9 14.25A1.25 1.25 0 0 0 10.25 13A1.25 1.25 0 0 0 9 11.75m6 0A1.25 1.25 0 0 0 13.75 13A1.25 1.25 0 0 0 15 14.25A1.25 1.25 0 0 0 16.25 13A1.25 1.25 0 0 0 15 11.75M12 2A10 10 0 0 0 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2m0 18c-4.41 0-8-3.59-8-8c0-.29 0-.58.05-.86c2.36-1.05 4.23-2.98 5.21-5.37a9.974 9.974 0 0 0 10.41 3.97c.21.71.33 1.47.33 2.26c0 4.41-3.59 8-8 8Z">
                                            </path>
                                        </svg> {{__("attr.male")}}</label>
                                </div>
                                <div>
                                    <input type="radio" class="btn-check" name="leads[{{$lead->id}}][gender]"
                                        id="lead-female-{{$lead->id}}" value="female" required />
                                    <label class="btn btn-outline-primary" for="lead-female-{{$lead->id}}">
                                        <svg viewBox="0 0 24 24" width="1.2em" height="1.2em" class="w-5 h-4">
                                            <path fill="currentColor"
                                                d="M13.75 13a1.25 1.25 0 1 1 2.5 0a1.25 1.25 0 0 1-2.5 0M22 12v10H2V12C2 6.5 6.5 2 12 2s10 4.5 10 10M4 12c0 4.41 3.59 8 8 8s8-3.59 8-8c0-.79-.12-1.55-.33-2.26A9.974 9.974 0 0 1 9.26 5.77c-.98 2.39-2.85 4.32-5.21 5.37c-.05.28-.05.57-.05.86m5 2.25a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5Z">
                                            </path>
                                        </svg> {{__("attr.female")}}
                                    </label>
                                </div>
                                @error('gender')
                                <div class="text-danger">* {{$message}}</div>
                                @enderror
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end mb-3">
        <button type="submit" class="btn btn-primary">
            @lang("translation.leads.transfer")
        </button>
    </div>
</form>
@endsection
@section('script')
<script src="{{asset('assets/js/inputmask/inputmask.js')}}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
            let selector = document.querySelectorAll(".phone");
            let im = new Inputmask("+\\9\\98(99)999-99-99", {'clearIncomplete': true, 'removeMaskOnSubmit': true});
            im.mask(selector);
        })
</script>
@endsection
