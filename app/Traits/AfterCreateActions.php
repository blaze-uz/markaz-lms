<?php

namespace App\Traits;

use App\Http\Requests\BaseRequest;
use Illuminate\Http\RedirectResponse;

trait AfterCreateActions
{
    public function afterCreate(BaseRequest $request, $entity)
    {
        if ($request->has("save")) return $this->save();
        if ($request->has("save_and_continue_editing")) return $this->saveAndContinueEditing($entity);
        if ($request->has("save_and_add_another")) return $this->saveAndAddAnother();
    }

    public function saveAndContinueEditing($entity): RedirectResponse
    {
        session()->flash('success_alert', __("translation.alert_messages.created"));
        return redirect()->route($this->edit_route, $entity->getUrlKey());
    }

    public function save(): RedirectResponse
    {
        session()->flash('success_alert', __("translation.alert_messages.created"));

        return redirect()->route($this->index_route);
    }

    public function saveAndAddAnother(): RedirectResponse
    {
        session()->flash('success_alert', __("translation.alert_messages.created"));

        return redirect()->route($this->create_route);
    }
}
