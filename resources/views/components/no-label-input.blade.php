<div>
    <div class="form-group mb-3">
        <input name="{{ $name }}" type="{{ $type }}"
               class="form-control {{ $class }} @error($name) is-invalid @enderror"
               id="{{ $name }}"
               {{ $attributes }}
               @if($wire)
                   {{ $wire }}
               @endif
               placeholder="{{ $placeholder }}"
               {{ $attribute }}
               value="{!! $value !!}"
        >
        @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>
</div>
