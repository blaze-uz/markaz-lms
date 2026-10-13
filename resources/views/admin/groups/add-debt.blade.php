@extends('layouts.master')
@section('title')
    @lang('translation.groups.add_debt')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <form action="{{ route('admin.groups.store-debt', $item->id) }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header">
                @lang("translation.groups.group_info")
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 col-sm-12">
                        <x-input
                            name="name"
                            label="{{ __('translation.groups.name') }}"
                            :value="$item->name"
                            attribute="disabled"
                        />
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <x-input name="date"
                                 type="date"
                                 label="{{ __('translation.groups.debt_for_which_date') }}"
                        />
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <x-money-input name="amount"
                                       label="{{ __('attr.amount') }}"
                                       :value="$item->price ?? old('amount')"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                @lang("translation.students.students")
            </div>
            <div class="card-body">
                @error('students')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <th>@lang("translation.students.full_name")</th>
                            <th>@lang("translation.groups.add_debt")</th>
                        </tr>
                        @foreach($item->students as $student)
                            <tr>
                                <td>{{ $student->id }}</td>
                                <td>{{ $student->getFullName() }}</td>
                                <td>
                                    <label>
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="students[]"
                                               value="{{ $student->id }}"
                                               checked
                                        />
                                    </label>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary"
                    onclick="return confirm('Are you sure?')">
                @lang("translation.btn.save")
            </button>
        </div>
    </form>
@endsection
@section('script')
    <script>
        document.querySelector('input[name="date"]').valueAsDate = new Date();
    </script>
@endsection
