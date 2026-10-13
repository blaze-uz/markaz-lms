<div>
    @push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.6.0/main.min.css">
    <style>
        .fc-license-message {
            display: none;
        }

        .fc-event {
            border-radius: 10px;
            /* Adjust the value as needed */
            padding: 5px;
        }

        .fc-title {
            font-size: 15px;
        }

        .fc-widget-content {
            height: 2rem !important;
        }

        .fc-today {
            background-color: rgba(0, 255, 235, 0.07) !important;
        }

        #calendar .fc-view {
            overflow-x: scroll;
        }

        @media (max-width: 768px) {
            #calendar .fc-view>table {
                width: 200%;
            }
        }

        .fc-scroller {
            height: auto !important;
            overflow-y: scroll !important;
            overflow-x: scroll !important;
        }

        .fc th {
            padding: 10px 0;
            vertical-align: middle;
            background: #fff;
            border: 1px solid #ddd;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .fc th:hover {
            background: #f5f5f5;
        }

        .fc-thead {
            position: sticky !important;
            top: 0;
            z-index: 1;
            background-color: #fff;
        }

        .no-data-message {
            text-align: center;
            font-size: 18px;
            color: #777;
            margin-top: 20px;
        }
    </style>
    @endpush
    <div class="p-1 card rounded-4">
        <div class="card-header">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div class="card-title">
                    @lang("translation.calendar")
                </div>
                <div class="w-auto">
                    <select name="week_day" id="week_day" class="form-control form-select" wire:model="day"
                        wire:change.debounce.500ms="changeDay">
                        <option value="1">@lang("attr.monday")</option>
                        <option value="2">@lang("attr.tuesday")</option>
                        <option value="3">@lang("attr.wednesday")</option>
                        <option value="4">@lang("attr.thursday")</option>
                        <option value="5">@lang("attr.friday")</option>
                        <option value="6">@lang("attr.saturday")</option>
                        <option value="7">@lang("attr.sunday")</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="no-data-message" class="no-data-message" style="display: none;">
                <x-no-result />
            </div>
            <div id="calendar" wire:ignore></div>
        </div>
        <div class="card-preloader d-none" wire:loading.class.remove="d-none">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
    @push('custom-scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.6.0/main.min.js"></script>
    @script
    <script>
        $wire.on('refreshCalendar', (events) => {
                var calendarEl = document.getElementById('calendar');
                var noDataMessageEl = document.getElementById('no-data-message');

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialDate: events.day,
                    timeZone: 'UTC',
                    initialView: 'resourceTimeline',
                    datesAboveResources: true,
                    slotMinTime: '06:00:00',
                    slotMaxTime: '23:59:59',
                    height: 'auto',
                    headerToolbar: {
                        left: '',
                        center: 'title',
                        right: ''
                    },
                    buttonText: {
                        today: '{{ __("translation.today") }}',
                    },
                    slotLabelFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false,
                    },
                    views: {
                        resourceTimeGridFourDay: {
                            type: 'resourceTimeGrid',
                            duration: {days: 3},
                            buttonText: '3 days'
                        },
                    },
                    resourceAreaHeaderContent: '{{ __("translation.rooms.rooms") }}',
                    resources: {!! json_encode($resources) !!},
                    events: events.events,
                    eventClick: function (info) {
                        let eventInfo = info.event.title + "\n" + info.event.extendedProps.description;
                        alert(eventInfo);
                    },
                });
                // Initial render
                calendar.render();

                // Listen for the refreshCalendar event
                window.addEventListener('refreshCalendar', function (event) {
                    var newEvents = event.detail.events;

                    // Remove existing events
                    calendar.removeAllEvents();

                    // Add new events
                    calendar.addEventSource(newEvents);

                    // Re-render calendar
                    calendar.render();
                });

                // Check if there are any resources
                if (calendar.getResources().length === 0) {
                    noDataMessageEl.style.display = 'block';
                    calendarEl.style.display = 'none';
                } else {
                    noDataMessageEl.style.display = 'none';
                    calendarEl.style.display = 'block';
                }
            });
    </script>
    @endscript
    @endpush
</div>