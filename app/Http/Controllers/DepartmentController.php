<?php

namespace App\Http\Controllers;

use App\DTOs\Department\DepartmentDTO;
use App\DTOs\Department\DepartmentFilterDTO;
use App\Http\Requests\Department\DepartmentRequest;
use App\Models\User;
use App\Services\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends BaseController
{
    protected string $view_folder = 'admin.departments';

    protected string $index_route = 'admin.departments.index';

    protected string $create_route = 'admin.departments.create';

    protected string $edit_route = 'admin.departments.edit';

    protected $dto = DepartmentDTO::class;

    protected $filterDto = DepartmentFilterDTO::class;

    protected $requestClass = DepartmentRequest::class;

    protected $serviceClass = DepartmentService::class;

    public function store(Request $request)
    {
        $requestClass = $this->getRequestClass();

        $request = new $requestClass($request->all());
        try {
            $this->service->create($this->dto::fromRequest($request));
        } catch (\Exception $exception) {
            session()->flash('error_alert', $exception->getMessage());

            return redirect()->back();
        }

        session()->flash('success_alert', __('translation.alert_messages.created'));

        return redirect()->route($this->index_route);
    }

    public function create()
    {
        $users = User::all();

        return view($this->getViewPath('create'), compact('users'));
    }

    public function edit($id)
    {
        $item = $this->service->find($id, ['users']);
        $users = User::all();

        return view($this->getViewPath('edit'), compact('item', 'users'));
    }

    public function destroy($id)
    {
        $this->service->destroy($id);
        session()->flash('success_alert', __('translation.alert_messages.deleted'));

        return redirect()->route($this->index_route);
    }
}
