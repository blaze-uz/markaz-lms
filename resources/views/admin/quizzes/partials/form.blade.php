<div class="card-body">
    <x-input :label="__('translation.quizzes.name')" name="name" :placeholder="__('translation.quizzes.name')"
        :value="$item->name ?? old('name') ?? ''" />
    <div class="mb-3 form-group">
        <label for="tests_count">
            @lang('translation.quizzes.tests_count')
        </label>
        <input type="number" class="form-control @error('tests_count') is-invalid @enderror" id="tests_count"
            name="tests_count" value="{{ $item->tests_count ?? old('tests_count') ?? '' }}">
        @error('tests_count')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @else
        <div class="form-text">
            @lang('translation.quizzes.tests_count_description')
        </div>
        @enderror
    </div>
    <div class="mb-3 form-group">
        <label for="passing_percentage">
            @lang('translation.quizzes.passing_percentage')
        </label>
        <input type="number" class="form-control @error('passing_percentage') is-invalid @enderror"
            id="passing_percentage" name="passing_percentage"
            value="{{ $item->passing_percentage ?? old('passing_percentage') ?? '' }}">
        @error('passing_percentage')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @else
        <div class="form-text">
            @lang('translation.quizzes.passing_percentage_description')
        </div>
        @enderror
    </div>
    <x-text-area :label="__('translation.quizzes.description')" name="description"
        :value="$item->description ?? old('description') ?? ''" />
</div>