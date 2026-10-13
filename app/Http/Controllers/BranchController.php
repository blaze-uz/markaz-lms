<?php

namespace App\Http\Controllers;

use App\DTOs\Branch\BranchDTO;
use App\DTOs\Branch\BranchFilterDTO;
use App\Http\Requests\Branch\BranchRequest;
use App\Services\BranchService;
use App\Traits\AfterCreateActions;
use Illuminate\Http\Request;

class BranchController extends BaseController
{
    use AfterCreateActions;

    protected string $view_folder = 'admin.branches';
    protected string $index_route = 'admin.branches.index';
    protected string $create_route = 'admin.branches.create';
    protected string $edit_route = 'admin.branches.edit';

    protected $dto = BranchDTO::class;

    protected $filterDto = BranchFilterDTO::class;

    protected $requestClass = BranchRequest::class;

    protected $serviceClass = BranchService ::class;

    public function changeCurrent(Request $request)
    {
        $this->service->changeCurrent($request->all());
        session()->flash('success_alert', __('translation.alert_messages.current_branch_changed'));
        return redirect()->back();
    }
}
