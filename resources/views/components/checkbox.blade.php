<div class="form-group my-3">
    <div class="form-check form-switch form-switch-success">
        <label for="SwitchCheck-{{$id ?? 'check'}}"></label>
        <input class="form-check-input" type="checkbox" role="switch" id="SwitchCheck-{{$id ?? 'check'}}"
               name="{{$name ?? 'switch'}}" {{$checked ? 'checked' : ''}}>
        <label class="form-check-label" for="SwitchCheck-{{$id ?? 'check'}}">
            {{$label ?? 'Switch'}}
        </label>
    </div>
    @error($name ?? 'switch')
    <div class="invalid-feedback">
        {{$message}}
    </div>
    @enderror
</div>
