<div>
    <div class="form-group mb-3">
        <label for="{{ $name }}">{{$label}}</label>
        <input type="tel" id="{{$name}}" name="{{$name}}"
               autocomplete="off"
               wire:model.live="{{$name}}"
               class="form-control phone {{($class) ?? ''}} @error($name) is-invalid @enderror"
               {{$attribute ?? ''}} value="{{($value) ?? ''}}" placeholder="{{$label}}">
        @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>
    @push('custom-scripts')
        <script src="{{asset('assets/js/inputmask/inputmask.js')}}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let selector = document.querySelectorAll(".phone");
                let im = new Inputmask("+\\9\\98(99)999-99-99", {'clearIncomplete': true, 'removeMaskOnSubmit': true});
                im.mask(selector);
            })
        </script>
    @endpush
</div>
