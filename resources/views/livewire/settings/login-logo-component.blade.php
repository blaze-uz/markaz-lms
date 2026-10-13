<div class="w-[200px] h-[100px] mx-auto flex items-center justify-center">
    @if($logo)
        <img
            src="{{ $logo }}"
            alt="Logo"
            style="max-width: 200px; max-height: 100px; width: auto; height: auto;"
            class="object-contain"
        >
    @else
        <img
            src="{{ asset('images/markaz-lms.svg') }}"
            alt="Logo"
            style="max-width: 200px; max-height: 100px; width: auto; height: auto;"
            class="object-contain hidden dark:block"
        >
        <img
            src="{{ asset('images/markaz-lms.svg') }}"
            alt="Logo"
            style="max-width: 200px; max-height: 100px; width: auto; height: auto;"
            class="object-contain block dark:hidden"
        >
    @endif
</div>
