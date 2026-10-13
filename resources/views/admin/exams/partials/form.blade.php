<div class="row">
    <div class="col-md-4 col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">@lang("translation.exams.add")</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">@lang("translation.exams.name") <span
                                class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           required>
                    @error('name')
                    <span class="text-danger">
                        * {{ $message }}
                    </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="tests_count" class="form-label">
                        @lang("translation.exams.tests_count")
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control @error('tests_count') is-invalid @enderror"
                           id="tests_count"
                           name="tests_count" required>
                    @error('tests_count')
                    <span class="text-danger">
                        * {{ $message }}
                    </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">@lang("translation.exams.description")
                        (@lang("translation.optional"))</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                    @error('description')
                    <span class="text-danger">
                        * {{ $message }}
                    </span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8 col-sm-12">
        <div class="card card-height-100">
            <div class="card-header">
                <h4 class="card-title">@lang("translation.exams.details")</h4>
            </div>
            <div class="card-body exam-detail-card">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="subject" class="form-label">
                                @lang("translation.exams.subject")
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="subject" name="exam_details[subject][]"
                                   required>
                            @error('exam_details.subject')
                            {{ $message }}
                            @enderror
                        </div>
                        <div class="col-md-5">
                            <label for="score" class="form-label">
                                @lang("translation.exams.score") (@lang("translation.exams.for_each_answer")) max: 99
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="score" name="exam_details[score][]" required>
                            @error('exam_details.score')
                            {{ $message }}
                            @enderror
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-primary mt-4 add-exam-detail">
                                <i class="ri-add-line align-bottom me-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addButton = document.querySelector('.add-exam-detail');
            const container = document.querySelector('.exam-detail-card');

            addButton.addEventListener('click', function () {
                const row = document.createElement('div');
                row.classList.add('row', 'mb-3');

                row.innerHTML = `
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="exam_details[subject][]" required>
                    </div>
                    <div class="col-md-5">
                        <input type="number" class="form-control" name="exam_details[score][]" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-field">
                            <i class="ri-delete-bin-6-line"></i>
                        </button>
                    </div>
                `;

                container.appendChild(row);

                const removeButtons = container.querySelectorAll('.remove-field');
                removeButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        this.parentElement.parentElement.remove();
                    });
                });
            });
        });
    </script>
@endpush
