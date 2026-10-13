<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                @lang("translation.groups.group_times")
            </div>
        </div>
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
    @push('custom-scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.9/index.global.min.js"
                integrity="sha512-XcSx5820pzZbdZYdvoBBKzuOivQv7oQMd+7JuUHh0jhMwqsWHOf+yRfZRxCtV0ySEKWtKblijTdl9pvODcmD7A=="
                crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
            let calendarEl = document.getElementById('calendar');
            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                initialDate: '{{\Carbon\Carbon::now()->format('Y-m-d')}}',
                slotMinTime: '06:00:00',
                slotMaxTime: '23:59:59',
                height: 'auto',
                slotLabelFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                },
                firstDay: 1,
                nowIndicator: true,
                editable: false,
                droppable: false,
                selectable: false,
                navLinks: true,
                allDaySlot: false,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: '{{__("attr.today")}}',
                    month: '{{__("attr.month")}}',
                    week: '{{__("attr.week")}}',
                    day: '{{__("attr.day")}}',
                },
                events: '{{route('admin.groups.calendar', $group->id)}}',
            });
            calendar.render();
        </script>
    @endpush
</div>
