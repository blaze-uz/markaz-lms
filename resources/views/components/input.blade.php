@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'class' => '',
    'attribute' => '',
    'disabled' => false
])

<div>
    <div class="form-group mb-3">
        <label class="form-label" for="{{ $name }}">{!! $label !!}</label>
        <input name="{{ $name }}" 
               type="{{ $type }}"
               class="form-control {{ $class }} @error($name) is-invalid @enderror"
               id="{{ $name }}"
               placeholder="{{ $placeholder }}"
               value="{!! $value !!}"
               {{ $attribute }}
               {{ $disabled ? 'disabled' : '' }}
               {{ $attributes }}
        >
        @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
        
        @if($disabled && $type !== 'hidden')
        <small class="text-muted">
            <i class="ri-lock-line"></i> {{ __('translation.field_disabled') }}
        </small>
        @endif
    </div>
</div>
