<?php

namespace App\Http\Controllers;

use App\DTOs\Role\RoleDTO;
use App\DTOs\Role\RoleFilterDTO;
use App\Http\Requests\Role\RoleRequest;
use App\Services\RoleService;

class RoleController extends BaseController
{
    protected string $view_folder = 'admin.roles';
    protected string $index_route = 'admin.roles.index';
    protected string $create_route = 'admin.roles.create';
    protected string $edit_route = 'admin.roles.edit';

    protected $dto = RoleDTO::class;

    protected $filterDto = RoleFilterDTO::class;

    protected $requestClass = RoleRequest::class;

    protected $serviceClass = RoleService::class;

    public function create()
    {
        $permissions = $this->service->getPermissions();

        return view($this->getViewPath('create'), compact('permissions'));
    }

    public function edit($id)
    {
        $item = $this->service->find($id);
        $permissions = $this->service->getPermissions();

        return view($this->getViewPath('edit'), compact('item', 'permissions'));
    }
}
