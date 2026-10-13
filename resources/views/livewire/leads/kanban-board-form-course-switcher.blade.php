<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" {{ $item->form_course_state ?
    'checked' : '' }} wire:change="updateFormCourseState">
</div>