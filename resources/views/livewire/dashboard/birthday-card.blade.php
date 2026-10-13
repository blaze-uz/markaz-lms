<div>
    @if ($show)
        <div class="alert bg-success-subtle text-success mt-3">
            <div class="align-items-center d-flex justify-content-between">
                <span>
                    🎂 {{ __('translation.today_birthdays_exists') }}: {{ $students_count }}
                </span>
                <a class="btn btn-sm btn-success" href="{{ route('admin.birthdays') }}">
                    {{ __('translation.show') }}
                </a>
            </div>
        </div>
    @endif
</div>
