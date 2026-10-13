@props(['name', 'label', 'value' => '', 'placeholder' => null, 'class' => '', 'disabled' => false])

<div>
    <div class="form-group mb-3">
        <label for="{{ $name }}">{{ $label }}</label>
        <input type="tel" 
               id="{{ $name }}" 
               name="{{ $name }}"
               class="form-control money {{ $class }} @error($name) is-invalid @enderror"
               value="{{ $value }}" 
               placeholder="{{ $placeholder ?? $label }}"
               {{ $attributes }}
               {{ $disabled ? 'disabled' : '' }}>
        
        @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>
    
    @push('custom-scripts')
        <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.js"></script>
        <script>
            $(document).ready(function () {
                $('.money').mask('000,000,000,000', {reverse: true});
                
                // Apply visual styling for disabled money inputs
                $('.money:disabled').each(function() {
                    $(this).css({
                        'background-color': '#f8f9fa',
                        'cursor': 'not-allowed',
                        'color': '#6c757d',
                        'border-color': '#dee2e6'
                    });
                    
                    // Add a small indication icon next to disabled inputs
                    $(this).parent().find('label').append(
                        ' <i class="ri-lock-line text-muted" style="font-size: 0.875rem;" title="This field is locked"></i>'
                    );
                });
            });
        </script>
    @endpush
</div>
