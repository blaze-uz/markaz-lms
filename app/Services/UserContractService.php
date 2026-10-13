<?php

namespace App\Services;

use App\Models\UserContract;

class UserContractService
{
    public $model = UserContract::class;

    public function update(array $data, $user_id): void
    {
        if (!isset($data['auto_salary_status'])) {
            $this->model::where('user_id', $user_id)->delete();
            return;
        }
        $user_contract = $this->model::where('user_id', $user_id)->first();
        $data['amount'] = removeComma($data['amount']);
        if ($user_contract) {
            $user_contract->update($data);
        } else {
            $data['user_id'] = $user_id;
            $this->model::create($data);
        }
    }
}