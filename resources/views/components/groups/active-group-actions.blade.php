@props(['item'])

<a class="dropdown-item" href="{{ route('admin.groups.send-sms-view', $item->id) }}">
    <span class="mdi mdi-email-fast-outline text-primary"></span>
    @lang("translation.groups.send_sms")
</a>

@can('group-payment-notice')
    <a class="dropdown-item" href="{{ route('admin.groups.payment-notice-view', $item->id) }}">
        <span class="mdi mdi-alert text-primary"></span>
        @lang("translation.students.payment_notice")
    </a>
@endcan

@can('group-complete')
    <a class="cursor-pointer dropdown-item" data-bs-toggle="modal" data-bs-target="#groupCompleteModal">
        <span class="mdi mdi-alert-circle text-danger"></span>
        @lang("translation.groups.complete_group")
    </a>
@endcan
