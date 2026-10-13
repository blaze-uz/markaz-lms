<?php

namespace App\Services;

use App\Models\Group;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GroupBalancePaymentProcessor
{
    public function __construct(private ?GroupBalanceDistributor $distributor = null)
    {
    }

    /**
     * @return array{groups_count: int, processed_count: int, skipped_count: int, total_amount: int, processed: array<int, array<string, mixed>>, skipped: array<int, array<string, mixed>>}
     */
    public function process(int $causerId, bool $dryRun = false): array
    {
        $groups = $this->groupsWithPositiveBalance();
        $processed = [];
        $skipped = [];
        $totalAmount = 0;

        foreach ($groups as $group) {
            $amount = (int) $group->account_balance;

            if ($dryRun) {
                $teacherPercentage = $group->teacher_percentage ?? $group->teacher?->percentage ?? 0;
                $mainAmount = (int) ($amount * (100 - $teacherPercentage) / 100);
                $teacherAmount = $group->teacher?->account ? $amount - $mainAmount : 0;

                $processed[] = [
                    'group_id' => $group->id,
                    'teacher_id' => $group->teacher_id,
                    'amount' => $amount,
                    'teacher_amount' => $teacherAmount,
                    'main_amount' => $group->teacher?->account ? $mainAmount : $amount,
                    'dry_run' => true,
                ];
                $totalAmount += $amount;

                continue;
            }

            $result = DB::transaction(fn () => $this->distributor()->distribute($group, $causerId));

            if ($result) {
                $processed[] = $result;
                $totalAmount += (int) $result['amount'];
            } else {
                $skipped[] = [
                    'group_id' => $group->id,
                    'teacher_id' => $group->teacher_id,
                    'amount' => $amount,
                ];
            }
        }

        return [
            'groups_count' => $groups->count(),
            'processed_count' => count($processed),
            'skipped_count' => count($skipped),
            'total_amount' => $totalAmount,
            'processed' => $processed,
            'skipped' => $skipped,
        ];
    }

    /**
     * @return Collection<int, Group>
     */
    private function groupsWithPositiveBalance(): Collection
    {
        return Group::withTrashed()
            ->join('accounts', 'accounts.id', '=', 'groups.account_id')
            ->where('accounts.balance', '>', 0)
            ->orderBy('groups.teacher_id')
            ->orderBy('groups.id')
            ->select('groups.*', 'accounts.balance as account_balance')
            ->with(['teacher.account'])
            ->get();
    }

    private function distributor(): GroupBalanceDistributor
    {
        if (! $this->distributor) {
            $this->distributor = new GroupBalanceDistributor;
        }

        return $this->distributor;
    }
}
