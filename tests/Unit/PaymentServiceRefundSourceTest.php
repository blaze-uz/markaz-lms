<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Services\PaymentService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PaymentServiceRefundSourceTest extends TestCase
{
    public function test_it_refunds_from_group_account_when_group_still_has_the_money(): void
    {
        $groupAccount = $this->account(1, 1000);
        $teacherAccount = $this->account(2);
        $mainAccount = $this->account(3);

        $sources = $this->buildSources(1000, $groupAccount, true, $teacherAccount, $mainAccount, 60);

        $this->assertCount(1, $sources);
        $this->assertSame(1, $sources[0]['account']->id);
        $this->assertSame(1000, $sources[0]['amount']);
    }

    public function test_it_refunds_from_teacher_and_main_accounts_when_group_money_was_paid_out(): void
    {
        $groupAccount = $this->account(1, 0);
        $teacherAccount = $this->account(2);
        $mainAccount = $this->account(3);

        $sources = $this->buildSources(1000, $groupAccount, true, $teacherAccount, $mainAccount, 60);

        $this->assertCount(2, $sources);
        $this->assertSame(2, $sources[0]['account']->id);
        $this->assertSame(600, $sources[0]['amount']);
        $this->assertSame(3, $sources[1]['account']->id);
        $this->assertSame(400, $sources[1]['amount']);
    }

    public function test_it_uses_group_balance_first_then_refunds_remaining_amount_by_percentage(): void
    {
        $groupAccount = $this->account(1, 300);
        $teacherAccount = $this->account(2);
        $mainAccount = $this->account(3);

        $sources = $this->buildSources(1000, $groupAccount, true, $teacherAccount, $mainAccount, 60);

        $this->assertCount(3, $sources);
        $this->assertSame(1, $sources[0]['account']->id);
        $this->assertSame(300, $sources[0]['amount']);
        $this->assertSame(2, $sources[1]['account']->id);
        $this->assertSame(420, $sources[1]['amount']);
        $this->assertSame(3, $sources[2]['account']->id);
        $this->assertSame(280, $sources[2]['amount']);
    }

    /**
     * @return array<int, array{account: Account, amount: int|float}>
     */
    private function buildSources(
        int|float $amount,
        ?Account $groupAccount,
        bool $canUseGroupAccount,
        ?Account $teacherAccount,
        ?Account $mainAccount,
        int|float $teacherPercentage,
    ): array {
        $reflection = new ReflectionClass(PaymentService::class);
        $method = $reflection->getMethod('buildGroupRefundSources');
        $method->setAccessible(true);

        return $method->invoke(
            new PaymentService(),
            $amount,
            $groupAccount,
            $canUseGroupAccount,
            $teacherAccount,
            $mainAccount,
            $teacherPercentage,
        );
    }

    private function account(int $id, int|float $balance = 0): Account
    {
        $account = new Account([
            'balance' => $balance,
        ]);
        $account->id = $id;

        return $account;
    }
}
