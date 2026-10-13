<!-- Lead Modal -->
<div wire:ignore.self class="modal fade" id="reminderModal" tabindex="-1" aria-labelledby="reminderModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                @if(!isset($selectedLead->reminder))
                    <h1 class="modal-title fs-5"
                        id="reminderModalLabel">{{$selectedLead?->name}}@lang("translation.leads.add_reminder_to")</h1>
                @else
                    <h1 class="modal-title fs-5"
                        id="reminderModalLabel">{{$selectedLead?->name}}
                        - @lang("translation.leads.update_reminder")</h1>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(!isset($selectedLead->reminder))
                    <form action="{{route('admin.leads.add-reminder', $selectedLead?->id ?? 0)}}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="date">
                                            {{__("translation.leads.date")}}
                                        </label>
                                        <input type="date" name="reminder_date" id="date" class="form-control">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="time">
                                            {{__("translation.leads.time")}}
                                        </label>
                                        <input type="time" name="reminder_time" id="time" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment">
                                {{__("translation.leads.comment")}}
                            </label>
                            <textarea name="reminder_note" id="comment" rows="5" class="form-control"></textarea>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-success">
                                {{__("translation.btn.save")}}
                            </button>
                        </div>
                    </form>
                @else
                    <form action="{{ route('admin.leads.update-reminder', $selectedLead->reminder->id) }}"
                          method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" id="status">
                        <div class="d-flex justify-content-center mt-3 gap-3">
                            <button class="btn bg-success-subtle text-success"
                                    onclick="document.getElementById('status').value = 'completed';">
                                {{__("translation.leads.completed")}}
                            </button>
                            <button class="btn bg-danger-subtle text-danger"
                                    onclick="document.getElementById('status').value = 'pending';">
                                {{__("translation.leads.not_completed")}}
                            </button>
                        </div>
                    </form>
                @endif
            </div>
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
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const timeElement = document.getElementById('time');
                const dateElement = document.getElementById('date');

                function setTime() {
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    timeElement.value = `${hours}:${minutes}`;
                    dateElement.valueAsDate = now;
                    console.log('time is: ', `${hours}:${minutes}`);
                }

                setTime();
            });
        </script>
    @endpush
</div>