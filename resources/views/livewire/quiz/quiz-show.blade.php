<div>
    <div class="row" wire:ignore>
        <div class="col-md-6 col-sm-12">
            <div class="cursor-pointer card card-animate rounded-4" wire:click="changeTab('user_exam_results')">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-0 fw-medium text-muted">@lang("translation.quizzes.user_exams")</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{$quiz_results->total()}}">
                                    0
                                </span>
                            </h2>
                            <small class="text-muted">
                                @lang("translation.click_to_filter")
                                <i class="fa-solid fa-computer-mouse"></i>
                            </small>
                        </div>
                        <div>
                            <div class="flex-shrink-0 avatar-sm">
                                <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                    <span class="mdi mdi-clipboard-text-clock-outline text-info fs-3"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-12">
            <div class="cursor-pointer card card-animate rounded-4" wire:click="changeTab('tests')">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-0 fw-medium text-muted">@lang("translation.quizzes.tests_count")</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{$tests->total()}}">0</span>
                            </h2>
                            <small class="text-muted">
                                @lang("translation.click_to_filter")
                                <i class="fa-solid fa-computer-mouse"></i>
                            </small>
                        </div>
                        <div>
                            <div class="flex-shrink-0 avatar-sm">
                                <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                    <span class="mdi mdi-counter fs-3 text-info"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        @if ($selectedTab == 'user_exam_results')
        <h4>@lang("translation.quizzes.user_exam_results")</h4>
        <div class="card">
            <div class="card-header">
                <div>
                    <div>
                        <input class="form-control" type="search" placeholder="{{__('translation.search')}}"
                            aria-label="{{__('translation.search')}}"
                            wire:model.live.debounce.500ms="quiz_result_search">
                    </div>

                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive" id="user-exams">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <th>@lang("translation.unique_id")</th>
                            <th>@lang('translation.exams.score')</th>
                            <th>@lang('translation.status')</th>
                            <th>@lang('translation.started_at')</th>
                            <th>@lang('translation.finished_at')</th>
                            {{-- <th></th> --}}
                        </tr>
                        @forelse($quiz_results as $result)
                        <tr>
                            <td>{{ $result->id }}</td>
                            <td>{{ $result->unique_id }}</td>
                            <td>{{ $result->score }}</td>
                            <td>
                                <span
                                    class="badge bg-{{$result->status == 'passed' ? 'success' : 'danger'}}-subtle text-{{$result->status == 'passed' ? 'success' : 'danger'}}">
                                    <span class="mdi mdi-{{$result->status == 'passed' ? 'check' : 'close'}}"></span>
                                    {{ __('translation.quizzes.'.$result->status) }}
                                </span>
                            </td>
                            <td>{{ $result->started_at }}</td>
                            <td>{{ $result->finished_at }}</td>
                            {{-- <td>
                                <button class="btn btn-sm btn-primary-subtle text-primary">
                                    <i class="fa-solid fa-eye"></i>
                                    @lang("translation.quizzes.show")
                                </button>
                            </td> --}}
                        </tr>
                        @empty
                        <tr>
                            <td colspan="100" class="text-center">
                                <x-no-result />
                            </td>
                        </tr>
                        @endforelse
                    </table>
                    <div class="mt-3 d-flex justify-content-end">
                        {{ $quiz_results->links() }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if ($selectedTab == 'tests')
        <h4>@lang("translation.quizzes.tests")</h4>
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <x-search parent-class='m-0' />
                    <a href="{{route('admin.quizzes.create-test', $item->id)}}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus"></i>
                        @lang("translation.quizzes.create_test")
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive" id="tests">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <th>{{ __('translation.quizzes.question') }}</th>
                            <th></th>
                        </tr>
                        @forelse($tests as $test)
                        <tr>
                            <td>{{ $test->id }}</td>
                            <td style="max-width: 400px">{{ $test->question }}</td>
                            <td class="gap-2 d-flex text-nowrap">
                                <button class="btn btn-sm btn-primary" wire:click="openTestModal({{$test->id}})">
                                    <i class="fa-solid fa-eye"></i>
                                    @lang("translation.quizzes.show")
                                </button>
                                <form action="{{ route('admin.quizzes.destroy-test', $test)}}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit"
                                        onclick="return confirm('Are you sure?')">
                                        <i class="fa-solid fa-trash"></i>
                                        @lang("translation.quizzes.delete")
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="100" class="text-center">
                                <x-no-result />
                            </td>
                        </tr>
                        @endforelse
                    </table>

                    <div class="mt-3 d-flex justify-content-end">
                        {{ $tests->links() }}
                    </div>
                </div>
            </div>
            @endif

            <div class="card-preloader d-none" wire:loading.class.remove="d-none">
                <div class="card-status">
                    <div class="spinner-border text-success">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        @include('livewire.quiz.partials.test-modal')
        @push('custom-scripts')
        @script
        <script>
            $wire.on('openTestModal', () => {
            $('#testModal').modal('show');
        });
        </script>
        @endscript
        @endpush
    </div>