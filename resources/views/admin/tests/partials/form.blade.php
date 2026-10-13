<div class="form-group">
    <label for="question">{{__('translation.quizzes.question')}}</label>
    <textarea class="form-control" rows="3" placeholder="{{__('translation.quizzes.write_a_question')}} ..."
        id="question" name="question">{{old("question") ?? $item->question}}</textarea>
</div>
<div class="mt-3">
    <label>{{__('translation.quizzes.answers')}}</label>
    <div>
        <label class="flex-row row card card-body d-flex align-items-center" for="answer1">
            <div class="text-center col-1">
                <input name="is_correct" value="0" type="radio" id="answer1" class="d-inline-block" />
            </div>
            <div class="col-11">
                <input name="answers[0]" type="text" class="form-control d-inline-block">
            </div>
        </label>
    </div>
    <div>
        <label class="flex-row row card card-body d-flex align-items-center" for="answer2">
            <div class="text-center col-1">
                <input name="is_correct" value="1" type="radio" id="answer2" class="d-inline-block" />
            </div>
            <div class="col-11">
                <input name="answers[1]" type="text" class="form-control d-inline-block">
            </div>
        </label>
    </div>
    <div>
        <label class="flex-row row card card-body d-flex align-items-center" for="answer3">
            <div class="text-center col-1">
                <input name="is_correct" value="2" type="radio" id="answer3" class="d-inline-block" />
            </div>
            <div class="col-11">
                <input name="answers[2]" type="text" class="form-control d-inline-block">
            </div>
        </label>
    </div>
    <div>
        <label class="flex-row row card card-body d-flex align-items-center" for="answer4">
            <div class="text-center col-1">
                <input name="is_correct" value="3" type="radio" id="answer4" class="d-inline-block" />
            </div>
            <div class="col-11">
                <input name="answers[3]" type="text" class="form-control d-inline-block">
            </div>
        </label>
    </div>
</div>
@push('custom-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const radioButtons = document.querySelectorAll('input[name="is_correct"]');
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function () {
                radioButtons.forEach(r => {
                    const label = r.closest('label');
                    if (label) {
                        label.classList.remove('bg-success-subtle');
                    }
                });
                const selectedLabel = this.closest('label');
                if (selectedLabel) {
                    selectedLabel.classList.add('bg-success-subtle');
                }
            });
        });
    });
</script>
@endpush