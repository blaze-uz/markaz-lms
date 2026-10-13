<x-input label="{{ __('translation.sms.template_name') }}" name="name" placeholder="Yangi yil tabrigi"
    value="{{ $template->name ?? old('name') }}" />
<div class="mb-3">
    <label for="content">
        @lang('translation.sms.template')
    </label>
    <textarea class="form-control @error('content') is-invalid @enderror" name="content" id="content" rows="5"
        placeholder="{STUDENT} sizni yangi yil bilan tabriklaymiz! {STUDY_CENTER} o'quv markazi">{{ isset($template) ? $template->content : '' }}</textarea>
    <div class="d-flex justify-content-end mt-2">
        <span id="counter" class="text-muted">0 @lang('translation.sms._count_characters')</span>
    </div>
</div>
@push('custom-scripts')
    <script>
        //with vanilla js
        let content = document.getElementById('content');
        let counter = document.getElementById('counter');
        content.addEventListener('keyup', function() {
            counter.innerText = content.value.length + ' {{ __('translation.sms._count_characters') }}';
        });
    </script>
@endpush
