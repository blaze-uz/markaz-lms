@props(['item'])

<tr>
    <th>
        <span class="fw-medium">@lang("translation.groups.monthly_payment_type")</span>
    </th>
    <td>
        @if($item->payment_type)
            <x-badge variant="success">{{ __("translation.groups.{$item->payment_type}") }}</x-badge>
        @else
            <x-badge variant="danger">@lang("translation.groups.not_set")</x-badge>
        @endif
    </td>
</tr>

@if($item->payment_type === 'date_to_date')
    <x-groups.payment-info.date-to-date :item="$item" />
@elseif($item->payment_type === 'by_count_lessons')
    <x-groups.payment-info.by-count-lessons :item="$item" />
@endif

<tr>
    <th>
        <span class="fw-medium">@lang("translation.groups.end_date")</span>
    </th>
    <td>{{ dateFormat($item->getEndDate()) }}</td>
</tr>
