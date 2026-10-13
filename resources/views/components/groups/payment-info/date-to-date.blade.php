@props(['item'])

<tr>
    <th>
        <span class="fw-medium">
            @lang("translation.groups.payment_start_date")
        </span>
    </th>
    <td>{{ dateFormat($item->start_date) }}</td>
</tr>

<tr>
    <th>
        <span class="fw-medium">@lang("translation.groups.current_debt_range")</span>
    </th>
    <td class="text-nowrap">{{ $item->getPaymentDate() }}</td>
</tr>
