<!-- Test Modal -->
<div class="modal fade" id="testModal" tabindex="-1" aria-labelledby="testModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="testModalLabel">
                    @lang("translation.quizzes.test")
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="question" class="fs-5">
                    {{ $selected_test?->id }}. {{ $selected_test?->question }}
                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th>@lang("translation.quizzes.answer")</th>
                            <th>@lang("translation.quizzes.status")</th>
                        </tr>
                        @if ($selected_test)
                        @foreach($selected_test?->answers as $answer)
                        <tr>
                            <td>{{ $answer->answer }}</td>
                            <td>
                                @if($answer->is_correct)
                                <span class="mdi mdi-check fs-3 text-success"></span>
                                @else
                                <span class="mdi mdi-close fs-3 text-danger"></span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </table>
                </div>
            </div>
            <div class=" modal-footer">
                <button type="button" class="btn bg-primary-subtle text-primary" data-bs-dismiss="modal">
                    @lang("translation.Close")
                </button>
            </div>
        </div>
    </div>
</div>