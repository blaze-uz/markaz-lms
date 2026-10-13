<?php

namespace App\Http\Controllers;

use App\DTOs\Permission\PermissionDTO;
use App\DTOs\Permission\PermissionFilterDTO;
use App\Http\Requests\Permission\PermissionRequest;
use App\Services\PermissionService;

class PermissionController extends BaseController
{
    protected int $per_page = 40;
    protected string $view_folder = 'admin.permissions';
    protected string $index_route = 'admin.permissions.index';
    protected string $create_route = 'admin.permissions.create';
    protected string $edit_route = 'admin.permissions.edit';

    protected $dto = PermissionDTO::class;

    protected $filterDto = PermissionFilterDTO::class;

    protected $requestClass = PermissionRequest::class;

    protected $serviceClass = PermissionService::class;
}
