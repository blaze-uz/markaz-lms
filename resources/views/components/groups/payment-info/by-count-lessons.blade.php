@props(['item'])

<tr>
    <th>
        <span class="fw-medium">@lang("translation.groups.lesson_count")</span>
    </th>
    <td>{{ $item->lesson_count }}</td>
</tr>

<tr>
    <th>
        <span class="fw-medium">@lang("translation.groups.current_lesson")</span>
    </th>
    <td>{{ $item->getCurrentLesson() }}</td>
</tr>
