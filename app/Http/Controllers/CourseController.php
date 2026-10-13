<?php

namespace App\Http\Controllers;

use App\DTOs\Course\CourseDto;
use App\DTOs\Course\CourseFilterDTO;
use App\Http\Requests\Course\CourseRequest;
use App\Models\Branch;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends BaseController
{
    protected string $view_folder = 'admin.courses';
    protected string $index_route = 'admin.courses.index';
    protected string $create_route = 'admin.courses.create';
    protected string $edit_route = 'admin.courses.edit';

    protected $dto = CourseDTO::class;

    protected $filterDto = CourseFilterDTO::class;

    protected $requestClass = CourseRequest::class;

    protected $serviceClass = CourseService::class;

    public function store(Request $request)
    {
        $requestClass = $this->getRequestClass();

        $request = new $requestClass($request->all());
        try {
            $this->service->create($this->dto::fromRequest($request));
        } catch (\Exception $exception) {
            session()->flash("error_alert", $exception->getMessage());
            return redirect()->back();
        }

        session()->flash('success_alert', __("translation.alert_messages.created"));
        return redirect()->route($this->index_route);
    }

    public function create()
    {
        $user = auth()->user();
        $branches = $user->branches;
        return view($this->getViewPath("create"), compact('branches'));
    }

    public function edit($id)
    {
        $item = $this->service->find($id, ['branches']);
        $user = auth()->user();
        $branches = $user->branches;
        return view($this->getViewPath("edit"), compact('item', 'branches'));
    }

    public function destroy($id)
    {
        $this->service->destroy($id);
        session()->flash('success_alert', __("translation.alert_messages.deleted"));
        return redirect()->route($this->index_route);
    }
}
