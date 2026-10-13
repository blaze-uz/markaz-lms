<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserContractRequest;
use App\Services\UserContractService;

class UserContractController extends Controller
{
    public $service;

    public function __construct()
    {
        $this->service = new UserContractService();
    }

    public function update(UserContractRequest $request, $user_id)
    {
        $this->service->update($request->validated(), $user_id);
        session()->flash('success_alert', __('translation.alert_messages.updated'));
        return redirect()->route('admin.users.show', $user_id);
    }
}
