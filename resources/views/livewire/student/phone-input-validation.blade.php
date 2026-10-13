<div>
    <x-phone-input :label="__('translation.students.phone')"
                   name="phone"
                   class="phone-input-validation"
                   :placeholder="__('translation.students.phone')"
                   :value="old('phone') ?? ''"
    />

    @if(!empty($errorMessage))
        <div class="alert {{ $studentInOtherBranch ? 'alert-warning' : 'alert-danger' }} mt-3">
            {{$errorMessage}}
            @if($studentInOtherBranch)
                <div class="mt-2">
                    <strong>{{ __('translation.student_info') }}:</strong>
                    <ul class="mb-0">
                        <li>{{ __('translation.students.name') }}: {{ $studentInOtherBranch->name }} {{ $studentInOtherBranch->surname }}</li>
                        <li>{{ __('translation.branch') }}: {{ $branchName }}</li>
                    </ul>
                </div>
            @endif
        </div>
    @endif

    @push('custom-scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                let phoneInput = document.querySelector(".phone-input-validation");

                phoneInput.addEventListener('input', function (e) {
                    let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,2})(\d{0,3})(\d{0,2})(\d{0,2})/);

                    if (x[0].length === 12) {
                        @this.phone = e.target.value;
                        @this.search()
                    } else {
                        @this.errorMessage = '';
                    }
                });
            })
        </script>
    @endpush
</div>
