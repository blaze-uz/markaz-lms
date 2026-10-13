<div>
    <div class="form-group mb-3">
        <label for="{{$name}}" class="form-label">{{$label}}</label>
        <textarea class="form-control {{$class}}" {{ $attribute }} id="{{$name}}" rows="3" name="{{$name}}"
                  placeholder="{{$placeholder}}">{{$value}}</textarea>
    </div>
</div>
