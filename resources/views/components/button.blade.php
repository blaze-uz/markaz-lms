<div>
    <button class="btn {{$class}} d-flex gap-1 align-items-center" id="{{$id}}" type="button" @if($confirmation)  onclick="return confirm('Are you sure?')" @endif>
        <div class="spinner-grow text-light d-none" style="width: 1rem; height: 1rem;" role="status">
            <span class="sr-only"></span>
        </div>
        {{ $slot }}
    </button>
    @push('custom-scripts')
    <script>
        const btn = document.querySelector('#{{$id}}');
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('.spinner-grow').classList.remove('d-none');
            if (this.querySelector('i')) {
                this.querySelector('i').classList.add('d-none');
            }
            btn.disabled = true;
            this.closest('form').submit();
        });
    </script>
    @endpush
</div>