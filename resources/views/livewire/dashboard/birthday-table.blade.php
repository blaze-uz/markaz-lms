<div>
    <div class="mb-3 justify-content-between d-flex">
        <x-search parent-class="m-0" />
        <div class="gap-2 d-flex">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#settingsModal">
                <i class="fas fa-cog"></i>
                {{__("translation.settings")}}
            </button>
            <form action="{{route('admin.birthdays.send-sms')}}" method="post">
                @csrf
                <button type="submit" class="btn btn-primary">
                    🎂 {{__("translation.sms.send_wishes")}}
                </button>
                
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>{{__("translation.students.student")}}</th>
                            <th>{{__("translation.students.phone")}}</th>
                            <th>{{__("translation.students.birthday")}}</th>
                            <th>{{__("translation.age")}}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td>{{$student->id}}</td>
                            <td>
                                <a href="{{route('admin.students.show', $student->id)}}">
                                    {{$student->getFullName()}}
                                </a>
                            </td>
                            <td>
                                <a href="tel:{{$student->phone}}">
                                    {{$student->getFormattedPhone()}}
                                </a>
                            </td>
                            <td>{{$student->birthday}}</td>
                            <td>{{$student->getAge()}}</td>
                            <td>
                                <a href="{{route('admin.students.show', $student->id)}}" class="btn btn-primary btn-sm">
                                    {{__("translation.btn.show")}}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="100" class="text-center">
                                <x-no-result />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                {{$students->links()}}
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

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="settingsModalLabel">
                        {{__("translation.message_settings")}}
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.birthdays.set-template')}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="template">{{__("translation.sms.select_template")}}</label>
                            <select class="form-select" id="template" name="template_id">
                                <option value="" selected disabled>
                                    {{__("translation.sms.select_template")}}
                                </option>
                                @foreach ($sms_templates as $template)
                                <option value="{{$template->id}}" {{$template->id == $template_id ? 'selected' : ''}}>
                                    {{$template->name}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{__("translation.Close")}}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{__("translation.btn.save")}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>