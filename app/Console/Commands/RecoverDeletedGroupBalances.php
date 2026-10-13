<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\User;
use App\Services\GroupBalanceDistributor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecoverDeletedGroupBalances extends Command
{
    /**
     * @var string
     */
    protected $signature = 'groups:recover-deleted-balances
        {--since= : Only groups deleted on/after this date (Y-m-d)}
        {--until= : Only groups deleted on/before this date (Y-m-d)}
        {--group=* : Limit to specific group id(s)}
        {--dry-run : Preview the distribution without writing anything}';

    /**
     * @var string
     */
    protected $description = 'Distribute stranded balances of soft-deleted groups to their teachers and the main account';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $since = $this->option('since');
        $until = $this->option('until');
        $groupIds = $this->option('group');

        $groups = Group::onlyTrashed()
            ->join('accounts', 'accounts.id', '=', 'groups.account_id')
            ->where('accounts.balance', '>', 0)
            ->when($since, fn ($q) => $q->whereDate('groups.deleted_at', '>=', $since))
            ->when($until, fn ($q) => $q->whereDate('groups.deleted_at', '<=', $until))
            ->when(! empty($groupIds), fn ($q) => $q->whereIn('groups.id', $groupIds))
            ->orderByDesc('accounts.balance')
            ->select('groups.*', 'accounts.balance as account_balance')
            ->get();

        if ($groups->isEmpty()) {
            $this->info('No soft-deleted groups with a positive balance found.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '').'Found '.$groups->count().' group(s) with stranded balance.');

        $causer = User::query()->whereHas('roles')->first() ?? User::query()->first();
        if (! $causer) {
            $this->error('No user found to attribute the recovery transactions to.');

            return self::FAILURE;
        }

        // Authenticate so created_by is valid and auth-dependent code behaves correctly.
        auth()->login($causer);

        $distributor = new GroupBalanceDistributor;
        $rows = [];
        $skipped = [];
        $total = 0;
        $skippedTotal = 0;

        foreach ($groups as $group) {
            $amount = (int) $group->account_balance;
            $teacher = $group->teacher;

            // Only recover groups whose teacher is currently active and has an account.
            // Groups with a deleted/missing teacher are left untouched for manual review.
            if (! $teacher || ! $teacher->account) {
                $skipped[] = [
                    $group->id,
                    $group->teacher_id ?? '—',
                    $group->teacher_id ? 'teacher deleted / no account' : 'no teacher',
                    number_format($amount),
                    optional($group->deleted_at)->format('Y-m-d'),
                ];
                $skippedTotal += $amount;

                continue;
            }

            $teacherPercentage = $group->teacher_percentage ?? $teacher->percentage ?? 0;
            $mainAmount = (int) ($amount * (100 - $teacherPercentage) / 100);
            $teacherAmount = $amount - $mainAmount;

            $rows[] = [
                $group->id,
                $teacher->name,
                $teacherPercentage.'%',
                number_format($teacherAmount),
                number_format($mainAmount),
                number_format($amount),
                optional($group->deleted_at)->format('Y-m-d'),
            ];
            $total += $amount;

            if (! $dryRun) {
                DB::transaction(function () use ($distributor, $group, $causer) {
                    $salaryDate = $group->deleted_at ? $group->deleted_at->copy() : null;
                    $result = $distributor->distribute($group, $causer->id, $salaryDate);

                    Log::info('Recovered stranded group balance', [
                        'group_id' => $group->id,
                        'result' => $result,
                    ]);
                });
            }
        }

        auth()->logout();

        if (! empty($rows)) {
            $this->table(
                ['Group', 'Teacher', 'Pct', 'Teacher amount', 'Main amount', 'Total', 'Deleted'],
                $rows
            );
        }
        $this->info(($dryRun ? 'Would distribute' : 'Distributed').' for '.count($rows).' active-teacher group(s): '.number_format($total)." so'm");

        if (! empty($skipped)) {
            $this->newLine();
            $this->warn('SKIPPED '.count($skipped).' group(s) with a deleted/missing teacher — left untouched ('.number_format($skippedTotal)." so'm):");
            $this->table(
                ['Group', 'Teacher ID', 'Reason', 'Balance', 'Deleted'],
                $skipped
            );
        }

        if ($dryRun) {
            $this->warn('Dry-run only — nothing was written. Re-run without --dry-run to apply.');
        } else {
            $this->info('Done. Active-teacher balances distributed; skipped groups left for manual review.');
        }

        return self::SUCCESS;
    }
}
