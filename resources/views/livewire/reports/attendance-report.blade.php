<div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" wire:model="dateRange" class="form-control" id="daterange" placeholder="{{ __('Select Date Range') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" wire:model.debounce.300ms="searchQuery" class="form-control" placeholder="{{ __('Search students...') }}">
                        </div>
                        <div class="col-md-3">
                            <select wire:model="attendanceStatus" class="form-control">
                                <option value="">{{ __('All Students') }}</option>
                                <option value="absent">{{ __('Absent Students Only') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">{{ __('Total Attendance') }}</h5>
                                    <h3 class="mt-3 mb-3">{{ $stats['total'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">{{ __('Present') }}</h5>
                                    <h3 class="mt-3 mb-3">{{ $stats['present'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">{{ __('Absent') }}</h5>
                                    <h3 class="mt-3 mb-3">{{ $stats['absent'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">{{ __('Late') }}</h5>
                                    <h3 class="mt-3 mb-3">{{ $stats['late'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Student') }}</th>
                                    <th>{{ __('Present Days') }}</th>
                                    <th>{{ __('Absent Days') }}</th>
                                    <th>{{ __('Late Days') }}</th>
                                    <th>{{ __('Attendance Rate') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    <tr>
                                        <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                        <td>{{ $student->attendances->where('status', 'present')->count() }}</td>
                                        <td>{{ $student->attendances->where('status', 'absent')->count() }}</td>
                                        <td>{{ $student->attendances->where('status', 'late')->count() }}</td>
                                        <td>
                                            @php
                                                $total = $student->attendances->count();
                                                $present = $student->attendances->where('status', 'present')->count();
                                                $rate = $total > 0 ? round(($present / $total) * 100) : 0;
                                            @endphp
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar {{ $rate >= 75 ? 'bg-success' : ($rate >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                     role="progressbar"
                                                     style="width: {{ $rate }}%"
                                                     aria-valuenow="{{ $rate }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">{{ $rate }}%</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $students->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        $('#daterange').daterangepicker({
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            @this.set('dateRange', picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });
    });
</script>
@endpush
