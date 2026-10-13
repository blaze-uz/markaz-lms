<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteRequest;
use App\Traits\AfterCreateActions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    use AfterCreateActions;

    protected int $per_page = 20;

    protected string $base_view_folder = 'manage';

    protected string $view_folder = '';

    protected string $root_route = 'manage.index';

    protected string $module = '';

    public $service;
    protected $dto;
    protected $filterDto;

    protected $requestClass;
    protected $serviceClass = null;
    protected $permissionCheck = true;

    public function __construct()
    {
        if ($this->serviceClass) {
            $this->service = app()->make($this->serviceClass);

            $middleware = 'permission:' . $this->service->getModelName();
            if ($this->permissionCheck) {
                $this->middleware($middleware . '-index')->only('index');
                $this->middleware($middleware . '-show')->only('show');
                $this->middleware($middleware . '-create')->only('create');
                $this->middleware($middleware . '-store')->only('store');
                $this->middleware($middleware . '-edit')->only('edit');
                $this->middleware($middleware . '-update')->only('update');
                $this->middleware($middleware . '-delete')->only(['destroy', 'delete']);
            }
        }
    }

    protected function getViewPath($view = ''): string
    {
        return "$this->view_folder.$view";
    }

    public function index(Request $request): View
    {
        $query = $this->service->query();

        $filter = $this->filterDto::fromRequest($request);

        $query = $this->service->filter($query, $filter);

        $items = $query
            ->latest('id')
            ->paginate($this->per_page);

        return view($this->getViewPath("index"), compact('items', 'filter'));
    }

    public function create()
    {
        return view($this->getViewPath("create"));
    }

    public function store(Request $request)
    {
        $requestClass = $this->getRequestClass();

        $request = new $requestClass($request->all());
        try {
            $entity = $this->service->create($this->dto::fromRequest($request));
        } catch (\Exception $exception) {
            session()->flash("error_alert", $exception->getMessage());

            return redirect()->back();
        }

        return $this->afterCreate($request, $entity);
    }

    public function show($id)
    {
        $item = $this->service->find($id);
        return view($this->getViewPath("show"), compact('item'));
    }

    public function edit($id)
    {
        $item = $this->service->find($id);
        return view($this->getViewPath("edit"), compact('item'));
    }

    public function update(Request $request, $id)
    {
        $requestClass = $this->getRequestClass();
        $request = new $requestClass($request->all());
        $item = $this->service->find($id);
        try {
            $this->service->update($this->dto::fromRequest($request), $item);
        } catch (\Exception $exception) {
            session()->flash("error_alert", $exception->getMessage());
            return redirect()->back();
        }

        session()->flash("success_alert", __("translation.alert_messages.updated"));
        return redirect()->route($this->index_route);
    }

    public function delete(DeleteRequest $request, $id = null): RedirectResponse
    {
        try {
            $this->service->delete($request->input('ids'));
        } catch (\Exception $exception) {
            session()->flash("error_alert", $exception->getMessage());
        }
        session()->flash('success_alert', __("translation.alert_messages.deleted"));
        return redirect()->route($this->index_route);
    }

    public function getRequestClass()
    {
        if ($this->requestClass === null) {
            throw new \Exception("The request class is not set.");
        }

        return app($this->requestClass);
    }
}
