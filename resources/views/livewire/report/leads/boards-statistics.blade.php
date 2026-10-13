<div class="d-flex flex-column h-100">
    <div class="row h-100">
        <div class="col-lg-4 col-md-4">
            <div class="card card-height-100">
                <div class="card-header">
                    {{ __('translation.leads.leads') }}: {{ $managersTotalLeads }} {{ __('translation.count') }}
                </div>
                <div class="overflow-auto card-body" style="max-height: 400px">
                    <table class="table">
                        <tr>
                            <th>{{ __('translation.students.name') }}</th>
                            <th>{{ __('translation.tasks_count') }}</th>
                        </tr>
                        @foreach ($managers as $manager)
                            <tr>
                                <td>{{ $manager->name }}</td>
                                <td>{{ $manager->tasks_count }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
        @foreach ($collections as $collection)
            <div class="col-lg-4 col-md-4">
                <div class="card card-height-100">
                    <div class="card-header">
                        {{ $collection->name }} : {{ $collection->new_tasks_count }} {{ __('translation.count') }}
                    </div>
                    <div class="overflow-auto card-body" style="max-height: 400px">
                        <table class="table">
                            <tr>
                                <th>{{ __('translation.students.name') }}</th>
                                <th>{{ __('translation.tasks_count') }}</th>
                            </tr>
                            @foreach ($collection->boards as $board)
                                <tr>
                                    <td>{{ $board->name }}</td>
                                    <td>{{ $board->new_tasks_count }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td>{{ __('translation.not_attached') }}</td>
                                <td>{{ $collection->unsigned_tasks_count }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
