<?php

namespace App\Services;

use App\Enums\AccountEnum;
use App\Enums\StaffPaymentTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Jobs\SendSalaryTelegramNotification;
use App\Jobs\SendWithdrawTelegramNotification;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Group;
use App\Models\GroupTime;
use App\Models\StaffPayment;
use App\Models\StaffPenalty;
use App\Models\StaffSalary;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserContract;
use App\Traits\SendsTelegramNotifications;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserService extends BaseService
{
    use SendsTelegramNotifications;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function query(): Builder
    {
        return $this->baseQuery()->whereHas('branches', function ($query) {
            $query->where('branch_id', auth()->user()->current_branch_id);
        });
    }

    public function getRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::select('id', 'name')->orderBy('name')->get();
    }

    public function getBranches()
    {
        return Branch::select('id', 'name')->orderBy('name')->get();
    }

    public function getBalance($item)
    {
        // Check if user has an account, if not create one
        if (! $item->account) {
            $this->createAccountForUser($item);
            $item->refresh(); // Refresh to load the new account relationship
        }

        return $item->account->getBalance();
    }

    /**
     * Create an account for a user if it doesn't exist
     */
    private function createAccountForUser($user, $accountType = AccountEnum::STAFF)
    {
        $accountName = $user->name;

        // Check if there's a soft deleted account for this user that we can restore
        $existingAccount = Account::onlyTrashed()
            ->where('name', $accountName)
            ->where('account_code', $accountType->value)
            ->first();

        if ($existingAccount) {
            // Restore the soft deleted account
            $existingAccount->restore();
            $existingAccount->update([
                'balance' => 0,
                'status' => 'active',
            ]);
            $account = $existingAccount;
        } else {
            // Create a new account
            $account = Account::create([
                'name' => $accountName,
                'account_code' => $accountType->value,
                'balance' => 0,
                'status' => 'active',
            ]);
        }

        // Associate the account with the user
        $user->update(['account_id' => $account->id]);

        return $account;
    }

    /**
     * Ensure user has an account, create one if it doesn't exist
     * This is a public method that can be called from other services
     */
    public function ensureUserHasAccount($user, $accountType = AccountEnum::STAFF)
    {
        if (! $user->account) {
            return $this->createAccountForUser($user, $accountType);
        }

        return $user->account;
    }

    /**
     * Soft delete a user's account
     */
    public function deleteUserAccount($user)
    {
        if ($user->account) {
            // Check if account has any transactions
            $hasTransactions = \App\Models\Transaction::where('to_account_id', $user->account->id)
                ->orWhere('from_account_id', $user->account->id)
                ->exists();

            if ($hasTransactions) {
                throw new \Exception('Cannot delete account with existing transactions. Please transfer or remove transactions first.');
            }

            // Soft delete the account
            $user->account->delete();

            // Remove the account association from user
            $user->update(['account_id' => null]);

            return true;
        }

        return false;
    }

    /**
     * Restore a user's soft deleted account
     */
    public function restoreUserAccount($user, $accountType = AccountEnum::STAFF)
    {
        // Look for soft deleted account with user's name
        $deletedAccount = Account::onlyTrashed()
            ->where('name', $user->name)
            ->where('account_code', $accountType->value)
            ->first();

        if ($deletedAccount) {
            $deletedAccount->restore();
            $deletedAccount->update([
                'status' => 'active',
            ]);

            // Associate the restored account with the user
            $user->update(['account_id' => $deletedAccount->id]);

            return $deletedAccount;
        }

        return null;
    }

    public function getStudentsCount($item)
    {
        // Eager load the active groups and count the students directly in the query
        return $item->groups()
            ->where('state', 'active')
            ->withCount('students')
            ->get()
            ->sum('students_count');
    }

    /**
     * @throws Exception
     */
    public function createWithMedia($dto)
    {
        DB::beginTransaction();

        try {
            $data = $dto->all();
            $account = $this->createAccount($data['name']);
            $entity = $this->model::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'birthday' => $data['birthday'],
                'password' => bcrypt($data['password']),
                'percentage' => $data['percentage'] ?? '0',
                'stars' => $data['stars'] ?? 0,
                'account_id' => $account->id,
            ]);
            $entity->branches()->attach($data['branches']);
            if ($data['avatar']) {
                $entity->addMediaFromRequest('avatar')->toMediaCollection('avatar');
            }
            $entity->assignRole($data['role']);

            activity()
                ->performedOn($entity)
                ->causedBy(auth()->user())
                ->withProperties(['role' => $data['role'], 'branches' => $data['branches']])
                ->log('Look guys, I logged something');
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }

        DB::commit();

        return $entity;
    }

    /**
     * @throws Exception
     */
    public function updateWithMedia($dto, $entity)
    {
        DB::beginTransaction();

        try {
            $data = $dto->all();
            $entity->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'birthday' => $data['birthday'],
                'percentage' => $data['percentage'] ?? '0',
                'stars' => $data['stars'] ?? 0,
            ]);

            $entity->branches()->detach();
            $entity->branches()->attach($data['branches']);
            $this->updateAccount($entity);

            // Check if current branch is in the updated branches list
            $userBranchIds = $data['branches'];
            if (! empty($userBranchIds) && ! in_array($entity->current_branch_id, $userBranchIds)) {
                // Update current branch to the first branch in the list
                $entity->update(['current_branch_id' => $userBranchIds[0]]);
            }

            if (isset($data['password'])) {
                $entity->update(['password' => bcrypt($data['password'])]);
            }
            if ($data['avatar']) {
                $entity->clearMediaCollection('avatar');
                $entity->addMediaFromRequest('avatar')->toMediaCollection('avatar');
            }
            $entity->syncRoles($data['role']);

            activity()
                ->performedOn($entity)
                ->causedBy(auth()->user())
                ->withProperties($data)
                ->log('Look guys, I logged something');
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }

        DB::commit();

        return $entity;
    }

    /**
     * @throws Exception
     */
    public function delete($ids): void
    {
        DB::beginTransaction();
        try {
            $this->model::query()
                ->whereIn('id', $ids)
                ->each(function ($item) {
                    foreach ($item->roles as $role) {
                        $item->removeRole($role->name);
                    }
                    $item->clearMediaCollection('image');
                    $item->text_suggestions()->delete();
                    $item->refund_payments()->delete();
                    $item->penalties()->delete();
                    if ($item->account) {
                        $item->account->delete();
                    }
                    $item->delete();
                });
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }
    }

    public function payment(array $data, $id)
    {
        DB::beginTransaction();
        try {
            if (removeComma($data['amount']) > 999999999) {
                return [
                    'success' => false,
                    'message' => 'Amount is too big',
                ];
            }

            $result = match ($data['payment_type']) {
                'salary' => $this->salaryPayment($data, $id),
                'withdraw' => $this->withdraw($data, $id),
                'group' => $this->groupPayment($data, $id),
                default => null,
            };

            if (is_array($result) && ($result['success'] ?? true) === false) {
                DB::rollBack();

                return $result;
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Payment successful',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }
    }

    private function salaryPayment(array $data, $id): void
    {
        $user = $this->model::find($id);

        $from_account = Account::findOrFail($data['account_id']);
        $userAccount = $user->account;

        $staffPayment = StaffPayment::create([
            'user_id' => $id,
            'type' => StaffPaymentTypeEnum::SALARY->value,
            'payment_type' => $data['payment_type'],
            'amount' => removeComma($data['amount']),
            'created_by' => auth()->user()->id,
        ]);

        $transaction = $staffPayment->transactions()->create([
            'from_account_id' => $from_account->id,
            'to_account_id' => $userAccount->id,
            'amount' => removeComma($data['amount']),
            'type' => TransactionTypeEnum::TRANSFER->value,
            'created_by' => auth()->user()->id,
        ]);

        $this->staffSalaryDocument($id, [$transaction->id], 'income', $data['payment_month'] ?? null);
        $this->sendSalaryNotificationTelegram(removeComma($data['amount']), $user->id);
    }

    private function withdraw(array $data, $id): void
    {
        $user = $this->model::find($id);
        $userAccount = $user->account;

        $staffPayment = StaffPayment::create([
            'user_id' => $id,
            'type' => StaffPaymentTypeEnum::WITHDRAW->value,
            'payment_type' => 'withdraw',
            'amount' => removeComma($data['amount']),
            'created_by' => auth()->user()->id,
        ]);

        $transaction = $staffPayment->transactions()->create([
            'from_account_id' => $userAccount->id,
            'amount' => removeComma($data['amount']),
            'type' => TransactionTypeEnum::EXPENSE->value,
            'created_by' => auth()->user()->id,
        ]);

        $this->staffSalaryDocument($id, [$transaction->id], 'expense', $data['payment_month'] ?? null);
        $this->sendWithdrawNotificationTelegram(removeComma($data['amount']), $user->id);
    }

    private function groupPayment(array $data, $id)
    {
        $user = $this->model::find($id);

        $groups = $user->groups()->with(['account', 'branches'])->get()
            ->filter(fn ($group) => $group->account !== null);

        $total_amount = $groups->sum(fn ($item) => $item->account->getBalance());
        if ($total_amount == 0) {
            return [
                'success' => false,
                'message' => 'No balance',
            ];
        }

        $teacher_account = $user->account;
        if (! $teacher_account) {
            return [
                'success' => false,
                'message' => 'Teacher has no account',
            ];
        }

        $transactions = [];
        $staffPaymentTransactions = [];
        foreach ($groups as $group) {
            $main_account = $this->mainAccountFor($group);

            $teacher_percentage = $group->teacher_percentage ?? $user->percentage;
            $group_amount = $group->account->getBalance();
            $main_amount = intval($group_amount * (100 - $teacher_percentage) / 100);
            $teacher_amount = $group_amount - $main_amount;
            if ($main_amount > 0) {
                $main_transaction = Transaction::create([
                    'from_account_id' => $group->account->id,
                    'to_account_id' => $main_account->id,
                    'amount' => $main_amount,
                    'type' => TransactionTypeEnum::TRANSFER->value,
                    'created_by' => auth()->user()->id,
                ]);
                $transactions[] = $main_transaction->id;
            }
            if ($teacher_amount > 0) {
                $teacher_transaction = Transaction::create([
                    'from_account_id' => $group->account->id,
                    'to_account_id' => $teacher_account->id,
                    'amount' => $teacher_amount,
                    'type' => TransactionTypeEnum::TRANSFER->value,
                    'created_by' => auth()->user()->id,
                ]);
                $transactions[] = $teacher_transaction->id;
                $staffPaymentTransactions[] = $teacher_transaction->id;
            }
        }

        $staffPayment = StaffPayment::create([
            'user_id' => $id,
            'type' => StaffPaymentTypeEnum::GROUP->value,
            'payment_type' => 'group',
            'amount' => $total_amount,
            'created_by' => auth()->user()->id,
        ]);

        $staffPayment->transactions()->attach($transactions);
        $this->staffSalaryDocument($id, $staffPaymentTransactions);
    }

    /**
     * Where the study centre's share of a group's balance goes.
     *
     * A group belongs to its branches through HasBranch — there is no `branch`
     * relation on it, so the previous `$group->branch?->account_id` was always
     * null and every payout silently fell through to the code-1000 account.
     */
    private function mainAccountFor(Group $group): Account
    {
        $branch = $group->branches->first();

        if ($branch?->account_id && $branchAccount = Account::find($branch->account_id)) {
            return $branchAccount;
        }

        if ($mainAccount = Account::where('account_code', AccountEnum::MAIN->value)->first()) {
            return $mainAccount;
        }

        // An installation whose main account was never seeded would otherwise
        // have no destination at all. Opening one is what BranchService does
        // for a new branch, and it costs less than stranding the payout.
        $created = Account::create([
            'name' => $branch?->name ?? 'Asosiy hisob',
            'account_code' => AccountEnum::MAIN->value,
        ]);

        $branch?->update(['account_id' => $created->id]);

        return $created;
    }

    public function changePassword(array $data): void
    {
        //if env is demo then return
        if (env('APP_ENV') == 'demo') {
            return;
        }
        $user = auth()->user();
        $user->update([
            'password' => bcrypt($data['password']),
        ]);
    }

    public function changeProfile(array $data): void
    {
        //if env is demo then return
        if (env('APP_ENV') == 'demo') {
            return;
        }
        $user = auth()->user();

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'birthday' => $data['birthday'] ?? null,
        ]);
        if (isset($data['avatar']) && $data['avatar']) {
            $user->clearMediaCollection('avatar');
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }
    }

    public function getCalendarEvents($id): array
    {
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');

        $colors = [
            '#6A9C89',
            '#1AACAC',
            '#8294C4',
            '#5C5470',
            '#1AACAC',
            '#DFA67B',
            '#654E92',
        ];

        $events = [];

        // Loop through the days in the specified date range
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            // Determine the day of the week (e.g., Monday, Tuesday) based on the date
            $dayOfWeek = date('l', strtotime($currentDate));

            // Retrieve the data for the specific day of the week
            $groups = $this->model::find($id)->groups()->get();
            $data = GroupTime::with('group')->whereIn('group_id', $groups->pluck('id'))
                ->where('name', strtolower($dayOfWeek)) // 'name' contains the day of the week
                ->get();

            if ($data) {
                foreach ($data as $item) {
                    $events[] = [
                        'id' => rand(1, 100),
                        'start' => "$currentDate $item->start:00",
                        'end' => "$currentDate $item->end:00",
                        'title' => $item->group->name,
                        'description' => $item->start.' - '.$item->end,
                        'color' => $colors[rand(0, 6)],
                        'textColor' => '#ffffff',
                    ];
                }
            }

            // Move to the next day
            $currentDate = date('Y-m-d', strtotime($currentDate.' +1 day'));
        }

        return $events;
    }

    public function isDeveloperAccountView($id): void
    {
        $user = $this->model::find($id);
        if (! $user) {
            abort(404);
        }
        match ($user->email) {
            'developer' => abort(403),
            default => null,
        };
    }

    private function createAccount($name)
    {
        return Account::create([
            'name' => $name,
            'account_code' => AccountEnum::STAFF->value,
        ]);
    }

    private function updateAccount($entity)
    {
        $entity->account->update([
            'name' => $entity->name,
        ]);
    }

    public function getAccounts()
    {
        return Account::whereIn('account_code', [AccountEnum::STAFF->value, AccountEnum::MAIN->value])->get();
    }

    public function getGroups($item)
    {
        return $item->groups()
            // teacher_percentage drives the split the payout actually makes;
            // leaving it out of the select made the modal quote the user's
            // default percentage for every group.
            ->select('id', 'name', 'account_id', 'teacher_percentage')
            ->with('account')
            ->withCount('students')
            ->get();
    }

    public function getGroupsCount($item)
    {
        return $item->groups()->withoutGlobalScope('branch')->count();
    }

    public function deletePayment($user_id, $transaction_id)
    {
        $user = $this->model::find($user_id);
        $transaction = Transaction::find($transaction_id);
        if ($transaction->from_account_id == $user->account->id || $transaction->to_account_id == $user->account->id) {
            $transaction->delete();
        }

        return $transaction;
    }

    /**
     * @param  array<int>  $staffPaymentTransactions
     */
    private function staffSalaryDocument($staffId, $staffPaymentTransactions, $type = 'income', ?string $forMonth = null): void
    {
        $date = $forMonth ? \Carbon\Carbon::parse($forMonth.'-01') : now();
        $currentMonth = $date->format('Y-m');

        $staffSalaryRecord = StaffSalary::where('staff_id', $staffId)
            ->where('date', 'LIKE', $currentMonth.'%')
            ->first();

        if (! $staffSalaryRecord) {
            $staffSalaryRecord = StaffSalary::create([
                'staff_id' => $staffId,
                'date' => $date,
                'causer_id' => auth()->id(),
            ]);
        }

        $transactions = Transaction::whereIn('id', $staffPaymentTransactions)->pluck('amount', 'id');

        $attachData = [];
        foreach ($staffPaymentTransactions as $transactionId) {
            $attachData[$transactionId] = [
                'type' => $type,
                'amount' => $transactions->get($transactionId),
                'causer_id' => auth()->id(),
            ];
        }

        $staffSalaryRecord->transactions()->syncWithoutDetaching($attachData);
    }

    public function getSalary($salary_id)
    {
        return StaffSalary::with(['staff', 'incomeTransactions' => function ($query) {
            $query->latest();
        }, 'expenseTransactions' => function ($query) {
            $query->latest();
        }])
            ->find($salary_id);
    }

    public function addPenalty(array $data, $id): void
    {
        DB::beginTransaction();
        try {
            $user = $this->model::find($id);
            //add expense transaction
            $transaction = $this->addTransactionForPenalty($data, $user);
            //add penalty record
            $user->penalties()->create([
                'transaction_id' => $transaction->id,
                'amount' => removeComma($data['amount']),
                'date' => $data['date'] ?? now(),
                'description' => $data['description'],
                'causer_id' => auth()->id(),
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage());
        }
        DB::commit();
    }

    private function addTransactionForPenalty(array $data, $user)
    {
        return Transaction::create([
            'from_account_id' => $user->account_id,
            'amount' => removeComma($data['amount']),
            'type' => TransactionTypeEnum::EXPENSE->value,
            'description' => $data['description'],
            'created_by' => auth()->user()->id,
        ]);
    }

    public function deletePenalty($id): void
    {
        DB::beginTransaction();
        try {
            $penalty = StaffPenalty::find($id);
            $penalty->transaction->delete();
            $penalty->delete();
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage());
        }
        DB::commit();
    }

    public function getUserContract($id)
    {
        return UserContract::where('user_id', $id)->first();
    }

    public function makeAuthKeyCache()
    {
        $user = auth()->user();
        cache()->forget('twa_auth_key_'.$user->id);

        $key = uniqid().'_'.$user->id;
        cache()->put('twa_auth_key_'.$user->id, $key);

        return $key;
    }

    private function sendSalaryNotificationTelegram($amount, $user_id)
    {
        $this->sendTelegramNotification(
            SendSalaryTelegramNotification::class,
            $amount,
            $user_id,
            'salary'
        );
    }

    private function sendWithdrawNotificationTelegram($amount, $user_id)
    {
        $this->sendTelegramNotification(
            SendWithdrawTelegramNotification::class,
            $amount,
            $user_id,
            'withdraw'
        );
    }
}
