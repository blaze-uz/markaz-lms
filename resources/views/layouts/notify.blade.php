<script>
    @if(session()->has('success_alert'))
    window.notifyMessage("{!! session()->get('success_alert') !!}", 'success')
    @elseif(session()->has('error_alert'))
    window.notifyMessage(`{!! session()->get('error_alert') !!}`, 'danger')
    @endif
</script>
