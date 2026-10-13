<?php

namespace App\Http\Controllers;

use App\DTOs\User\UserDTO;
use App\DTOs\User\UserFilterDTO;
use App\Http\Requests\User\PasswordRequest;
use App\Http\Requests\User\PaymentRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\UserRequest;
use App\Services\UserService;
use App\Traits\AfterCreateActions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    use AfterCreateActions;

    protected string $view_folder = 'admin.users';

    protected string $index_route = 'admin.users.index';

    protected string $create_route = 'admin.users.create';

    protected string $edit_route = 'admin.users.edit';

    protected $dto = UserDTO::class;

    protected $filterDto = UserFilterDTO::class;

    protected $requestClass = UserRequest::class;

    public $serviceClass = UserService::class;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('permission:user-payment-list')->only('paymentsList');
        $this->middleware('permission:salary-index')->only(['salaries', 'salaryShow']);
    }

    public function index(Request $request): View
    {
        $query = $this->service->query();

        $filter = $this->filterDto::fromRequest($request);

        $query = $this->service->filter($query, $filter);

        $roles = $this->service->getRoles();
        $departments = \App\Models\Department::all();

        $perPage = $request->get('per_page', $this->per_page);

        $items = $query
            ->with(['roles', 'groups', 'groups.students', 'media'])
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.users.index', [
            'items' => $items,
            'roles' => $roles,
            'departments' => $departments,
            'filter' => $filter,
        ]);
    }

    public function show($id)
    {
        $item = $this->service->find($id, ['groups', 'branches']);
        $balance = $this->service->getBalance($item);
        $studentsCount = $this->service->getStudentsCount($item);
        $accounts = $this->service->getAccounts();
        $groups = $this->service->getGroups($item);

        return view(
            'admin.users.show',
            [
                'item' => $item,
                'balance' => $balance,
                'studentsCount' => $studentsCount,
                'accounts' => $accounts,
                'groups' => $groups,
            ]
        );
    }

    public function create()
    {
        $roles = $this->service->getRoles();
        $branches = $this->service->getBranches();

        return view($this->getViewPath('create'), compact('roles', 'branches'));
    }

    public function edit($id)
    {
        $item = $this->service->find($id);
        $roles = $this->service->getRoles();
        $branches = $this->service->getBranches();
        $user_contract = $this->service->getUserContract($id);

        return view(
            'admin.users.edit',
            [
                'item' => $item,
                'roles' => $roles,
                'branches' => $branches,
                'user_contract' => $user_contract,
            ]
        );
    }

    public function store(Request $request)
    {
        $requestClass = $this->getRequestClass();

        $request = new $requestClass($request->all());
        try {
            $entity = $this->service->createWithMedia($this->dto::fromRequest($request));
        } catch (\Exception $exception) {
            session()->flash('error_alert', $exception->getMessage());

            return redirect()->back();
        }

        session()->flash(
            'success_alert',
            __('translation.alert_messages.created')
        );

        return redirect()->route('admin.users.show', $entity->id);
    }

    /**
     * @throws \Exception
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $requestClass = $this->getRequestClass();
        $request = new $requestClass($request->all());
        $item = $this->service->find($id);
        try {
            $this->service->updateWithMedia(
                $this->dto::fromRequest($request),
                $item
            );

            // Check if the user's current branch is in their branches list
            $user = $this->service->find($id);
            $userBranchIds = $user->branches()->pluck('branch_id')->toArray();

            if (! empty($userBranchIds) && ! in_array($user->current_branch_id, $userBranchIds)) {
                // Update current branch to the first branch in the list
                $user->update(['current_branch_id' => $userBranchIds[0]]);

                session()->flash(
                    'info_alert',
                    __('translation.alert_messages.branch_changed')
                );
            }
        } catch (\Exception $exception) {
            session()->flash('error_alert', $exception->getMessage());

            return redirect()->back();
        }

        session()->flash(
            'success_alert',
            __('translation.alert_messages.updated')
        );

        return redirect()->route('admin.users.show', $id);
    }

    public function paymentView($id)
    {
        $item = $this->service->find($id);
        $accounts = $this->service->getAccounts();
        $groups = $this->service->getGroups($item);

        return view(
            $this->getViewPath('payment'),
            compact('item', 'accounts', 'groups')
        );
    }

    public function payment(PaymentRequest $request, $id)
    {
        $response = $this->service->payment($request->validated(), $id);
        if (! $response['success']) {
            session()->flash('error_alert', $response['message']);

            return redirect()->back();
        }
        session()->flash('success_alert', __('translation.alert_messages.payment'));

        return redirect()->route('admin.users.show', ['user' => $id]);
    }

    public function profileSettings()
    {
        $item = auth()->user();
        //check any role exists
        if (! $item->roles->count()) {
            abort(403);
        }
        //make cache for user auth key
        $twa_key = $this->service->makeAuthKeyCache($item);

        return view(
            $this->getViewPath('profile.profile-settings'),
            [
                'item' => $item,
                'twa_key' => $twa_key,
            ]
        );
    }

    public function changePassword(PasswordRequest $request)
    {
        $this->service->changePassword($request->validated());
        session()->flash(
            'success_alert',
            __('translation.alert_messages.password_changed')
        );

        return redirect()->route('admin.profile.settings');
    }

    public function changeProfile(UpdateProfileRequest $request)
    {
        $this->service->changeProfile($request->validated());
        session()->flash(
            'success_alert',
            __('translation.alert_messages.profile_changed')
        );

        return redirect()->route('admin.profile.settings');
    }

    public function getCalendarData($id)
    {
        $groupTimes = $this->service->getCalendarEvents($id);

        return response()->json($groupTimes);
    }

    public function paymentsList()
    {
        return view('admin.users.payments-list');
    }

    public function deletePayment($user_id, $payment_id)
    {
        $this->service->deletePayment($user_id, $payment_id);
        session()->flash(
            'success_alert',
            __('translation.alert_messages.payment_deleted')
        );

        return redirect()->route('admin.users.show', ['user' => $user_id]);
    }

    public function salaries()
    {
        //add middleware permission:salary-index

        return view('admin.users.salaries.index');
    }

    public function salaryShow($salary_id)
    {
        $salary = $this->service->getSalary($salary_id);
        if (! $salary) {
            abort(404);
        }
        $item = $this->service->find($salary->staff_id);

        return view('admin.users.salaries.show', [
            'item' => $item,
            'salary' => $salary,
        ]);
    }

    public function addPenalty(Request $request, $id)
    {
        $this->service->addPenalty($request->all(), $id);
        session()->flash('success_alert', __('translation.alert_messages.penalty_added'));

        return redirect()->route('admin.users.show', ['user' => $id]);
    }

    public function deletePenalty($id)
    {
        $this->service->deletePenalty($id);
        session()->flash('success_alert', __('translation.alert_messages.penalty_deleted'));

        return redirect()->back();
    }
}
