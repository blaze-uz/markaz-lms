<div>
    @push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css"
        integrity="sha512-H9jrZiiopUdsLpg94A333EfumgUBpO9MdbxStdeITo+KEIMaNfHNvwyjjDJb+ERPaRS6DpyRlKbvPUasNItRyw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @endpush
    @if ($bot_token->value)
    <div class="p-1 card rounded-4">
        <div class="border-0 card-header">
            <div class="row g-4 align-items-center">
                <div class="col-sm-auto">
                    <div class="gap-2 d-flex">
                        <div class="search-box">
                            <input name="search" type="search" class="form-control search w-100"
                                placeholder="@lang('translation.Search for...')"
                                wire:model.live.debounce.500ms="search">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="col-sm-auto ms-auto">
                    <div class="flex-wrap gap-2 hstack d-flex">
                        <button class="btn btn-primary settings" data-bs-toggle="modal" data-bs-target="#settingsModal">
                            <i class="fa-solid fa-gear"></i>
                            @lang("translation.quizzes.settings")
                        </button>
                        <a href="{{route('admin.quizzes.create')}}" class="btn btn-success">
                            <i class="fas fa-plus"></i> @lang("translation.quizzes.add")
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive table-card">
                @if($items->count())
                <table class="table align-middle" id="quiz-table">
                    <thead class="align-middle table-light">
                        <tr>
                            <th>ID</th>
                            <th>@lang("translation.quizzes.name")</th>
                            <th>@lang("translation.quizzes.tests_count")</th>
                            <th>@lang("translation.quizzes.passing_percentage")</th>
                            <th>@lang("translation.quizzes.created_by")</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="list form-check-all">
                        @foreach($items as $item)
                        <tr>
                            <td style="width: 25px">
                                {{ $item->id }}
                            </td>
                            <td class="text-nowrap text-truncate" style="max-width: 270px">
                                <a href="{{ route('admin.quizzes.show', $item->id) }}" class="text-reset">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td class="text-nowrap">
                                {{ $item->tests_count }}
                            </td>
                            <td class="text-nowrap">
                                {{ $item->passing_percentage }}%
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.users.show', $item->created_by ?? 0) }}" class="fw-medium">
                                    {{ $item->creator?->name ?? __('translation.students.unknown') }}
                                </a>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.quizzes.show', $item->id) }}"
                                    class="border btn btn-ghost-dark waves-effect waves-light material-shadow-none btn-sm t-show-btn">
                                    <i class="mdi mdi-eye fs-5"></i>
                                </a>
                                <a href="{{ route('admin.quizzes.edit', $item->id) }}"
                                    class="border btn btn-ghost-dark waves-effect waves-light material-shadow-none btn-sm t-edit-btn">
                                    <i class="mdi mdi-pencil fs-5"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <x-no-result />
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

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="settingsModalLabel">
                        @lang('translation.set_telegram_bot_token')
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.quizzes.set-token')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="bot_token">@lang('translation.telegram_bot_token')</label>
                            <input type="text" class="form-control" id="bot_token" name="bot_token"
                                value="{{ $bot_token->value }}">
                            @error('bot_token')
                            <div class="text-danger">{{ $message }}</div>
                            @else
                            <div class="text-muted">
                                @lang('translation.telegram_bot_token_hint')
                            </div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-secondary-subtle text-secondary" data-bs-dismiss="modal">
                            @lang('translation.Close')
                        </button>
                        <button type="submit" class="btn bg-primary-subtle text-primary">
                            @lang('translation.btn.save')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @else
    <div class=" row">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">@lang('translation.set_telegram_bot_token')</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.quizzes.set-token')}}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="bot_token">@lang('translation.telegram_bot_token')</label>
                            <input type="text" class="form-control" id="bot_token" name="bot_token">
                            @error('bot_token')
                            <div class="text-danger">{{ $message }}</div>
                            @else
                            <div class="text-muted">
                                @lang('translation.telegram_bot_token_hint')
                            </div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-success">@lang('translation.btn.save')</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">@lang('translation.telegram_bot_token_instruction')</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-muted">
                        @lang('translation.telegram_bot_token_instruction_text')
                    </div>
                    <div>
                        <a href="{{ asset('images/telegram-bot-instruction.svg') }}" class="instruction-image"
                            alt="Telegram bot instruction" data-fancybox="images">
                            <img src="{{ asset('images/telegram-bot-instruction.svg') }}" alt="Telegram bot instruction"
                                id="instruction-image" style="width: 300px; height: 200px" class="img-thumbnail">
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2"></div>
    </div>
    @endif
    @push('custom-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
    <script>
        $(".instruction-image").fancybox();
    </script>
    @endpush
</div>