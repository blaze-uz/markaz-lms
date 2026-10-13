@extends('layouts.master')
@section('title')
    @lang('translation.rooms-settings')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-3 col-sm-12"></div>
        <div class="col-md-6 col-sm-12">
            <form action="{{ route('admin.rooms.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <x-input label="{{__('translation.rooms.name')}}" name="name"
                                 placeholder="{{__('translation.rooms.name')}}" :value="$item->name"/>
                        <x-money-input type="tel" label="{!!__('translation.rooms.capacity')!!}" name="capacity"
                                       :value="$item->capacity ?? old('capacity') ?? ''"/>
                        <div class="form-group mb-3">
                            <label for="branch">
                                @lang("translation.groups.choose_branch")
                            </label>
                            <input type="text" class="form-control"
                                   value="{{auth()->user()->currentBranch?->name ?? 'Branch'}}" disabled>
                        </div>
                        <div class="form-group">
                            <label for="room-status">
                                @lang("translation.rooms.status")
                            </label>
                            <select name="status" id="room-status" class="form-control">
                                <option value="available"
                                        @if($item->status == 'available') selected @endif>@lang("translation.rooms.available")</option>
                                <option value="unavailable"
                                        @if($item->status == 'unavailable') selected @endif>@lang("translation.rooms.unavailable")</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        @lang("translation.btn.save")
                    </button>
                </div>
            </form>
        </div>
        <div class="col-md-3 col-sm-12"></div>
    </div>
@endsection
