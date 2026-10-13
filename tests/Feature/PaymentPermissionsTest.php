<?php

namespace Tests\Feature;

use App\Enums\AccountEnum;
use App\Jobs\Student\SendPaymentSmsNotification;
use App\Livewire\Payment\PaymentList;
use App\Livewire\Student\PaymentTable as StudentPaymentTable;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\ReceiptSetting;
use App\Models\RefundPayment;
use App\Models\Settings;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Who may act on a single payment.
 *
 * The admin route group only asks for a login, and
 * PaymentController used to guard nothing but its index. Any member of
 * staff — a Teacher included — could refund a payment by URL (which writes a
 * Transaction and a RefundPayment), change its payment type or send its SMS.
 * Each action now requires an existing payment permission, and the buttons
 * for them are hidden from anyone who lacks it.
 */
class PaymentPermissionsTest extends TestCase
{
    use DatabaseTransactions;

    private const AMOUNT = 150000;

    private Branch $branch;

    private PaymentType $cash;

    private PaymentType $card;

    private Account $studentAccount;

    private Student $student;

    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Test branch '.uniqid()]);
        $this->cash = PaymentType::create(['name' => 'Test cash '.uniqid()]);
        $this->card = PaymentType::create(['name' => 'Test card '.uniqid()]);

        $cashier = $this->userWithPermissions();

        $this->studentAccount = Account::create([
            'name' => 'Test student '.uniqid(),
            'account_code' => AccountEnum::STUDENT->value,
            'balance' => self::AMOUNT,
            'status' => 'active',
        ]);

        // Quietly: Student's creating hook stamps created_by_id from the
        // logged-in user, and nobody is logged in while fixtures are built.
        $student = new Student([
            'name' => 'Refund',
            'surname' => 'Student',
            'phone' => '901234567',
            'account_id' => $this->studentAccount->id,
            'created_by_id' => $cashier->id,
        ]);
        $student->saveQuietly();
        $student->branches()->attach($this->branch->id);
        $this->student = $student;

        $this->payment = Payment::create([
            'student_id' => $student->id,
            'payment_type_id' => $this->cash->id,
            'amount' => self::AMOUNT,
            'created_by' => $cashier->id,
            'date' => now(),
        ]);
        $this->payment->branches()->attach($this->branch->id);
    }

    public function test_a_teacher_cannot_refund_a_payment(): void
    {
        $transactions = Transaction::withTrashed()->count();
        $refunds = DB::table('refund_payments')->count();

        $this->actingAs($this->teacher())
            ->post(route('admin.payments.refund-store', $this->payment))
            ->assertForbidden();

        $this->assertSame($transactions, Transaction::withTrashed()->count());
        $this->assertSame($refunds, DB::table('refund_payments')->count());
        $this->assertSame(0, RefundPayment::withoutGlobalScopes()->where('payment_id', $this->payment->id)->count());

        $payment = $this->payment->fresh();
        $this->assertNotNull($payment);
        $this->assertSame(self::AMOUNT, (int) $payment->amount);
        $this->assertSame($this->cash->id, (int) $payment->payment_type_id);
        $this->assertSame(self::AMOUNT, (int) $this->studentAccount->fresh()->balance);
    }

    public function test_a_teacher_cannot_open_the_refund_page(): void
    {
        $this->actingAs($this->teacher())
            ->get(route('admin.payments.refund', $this->payment))
            ->assertForbidden();
    }

    public function test_a_teacher_cannot_change_the_payment_type(): void
    {
        $this->actingAs($this->teacher())
            ->put(route('admin.payments.update', $this->payment), ['payment_type_id' => $this->card->id])
            ->assertForbidden();

        $this->assertSame($this->cash->id, (int) $this->payment->fresh()->payment_type_id);
    }

    public function test_a_teacher_cannot_open_the_edit_page(): void
    {
        $this->actingAs($this->teacher())
            ->get(route('admin.payments.edit', $this->payment))
            ->assertForbidden();
    }

    public function test_a_teacher_cannot_send_the_payment_sms(): void
    {
        Bus::fake([SendPaymentSmsNotification::class]);
        $sms = DB::table('sms')->count();

        $this->actingAs($this->teacher())
            ->post(route('admin.payments.send-sms', $this->payment->id))
            ->assertForbidden();

        Bus::assertNotDispatched(SendPaymentSmsNotification::class);
        $this->assertSame($sms, DB::table('sms')->count());
    }

    public function test_a_teacher_cannot_open_a_payment(): void
    {
        $this->actingAs($this->teacher())
            ->get(route('admin.payments.show', $this->payment))
            ->assertForbidden();
    }

    public function test_payment_refund_alone_refunds_a_payment(): void
    {
        $user = $this->userWithPermissions('payment-refund');

        $this->actingAs($user)
            ->post(route('admin.payments.refund-store', $this->payment))
            ->assertRedirect(route('admin.payments.index'))
            ->assertSessionHas('success_alert');

        $refund = RefundPayment::withoutGlobalScopes()->where('payment_id', $this->payment->id)->sole();
        $this->assertSame(self::AMOUNT, (int) $refund->amount);
        $this->assertSame($user->id, (int) $refund->created_by);
        $this->assertTrue($refund->branches()->whereKey($this->branch->id)->exists());

        $transaction = Transaction::findOrFail($refund->transaction_id);
        $this->assertSame($this->studentAccount->id, (int) $transaction->from_account_id);
        $this->assertSame(self::AMOUNT, (int) $transaction->amount);
        $this->assertSame(0, (int) $this->studentAccount->fresh()->balance);
    }

    public function test_payment_refund_alone_opens_the_refund_page(): void
    {
        $this->actingAs($this->userWithPermissions('payment-refund'))
            ->get(route('admin.payments.refund', $this->payment))
            ->assertOk();
    }

    public function test_payment_edit_changes_the_payment_type(): void
    {
        $this->actingAs($this->userWithPermissions('payment-edit'))
            ->put(route('admin.payments.update', $this->payment), ['payment_type_id' => $this->card->id])
            ->assertRedirect(route('admin.payments.index'));

        $this->assertSame($this->card->id, (int) $this->payment->fresh()->payment_type_id);
    }

    public function test_payment_edit_opens_the_edit_page(): void
    {
        $this->actingAs($this->userWithPermissions('payment-edit'))
            ->get(route('admin.payments.edit', $this->payment))
            ->assertOk();
    }

    public function test_payment_index_sends_the_payment_sms(): void
    {
        Bus::fake([SendPaymentSmsNotification::class]);

        $this->actingAs($this->userWithPermissions('payment-index'))
            ->post(route('admin.payments.send-sms', $this->payment->id))
            ->assertRedirect();

        Bus::assertDispatched(SendPaymentSmsNotification::class);
    }

    /**
     * @dataProvider showPermissions
     */
    public function test_a_payment_opens_with_either_view_permission(string $permission): void
    {
        $this->actingAs($this->userWithPermissions($permission))
            ->get(route('admin.payments.show', $this->payment))
            ->assertOk();
    }

    public static function showPermissions(): array
    {
        return [
            'payment-index' => ['payment-index'],
            'payment-show' => ['payment-show'],
        ];
    }

    /**
     * @dataProvider printPermissions
     */
    public function test_the_receipt_prints_with_any_one_of_its_permissions(string $permission): void
    {
        $this->withReceiptSettings();

        $this->actingAs($this->userWithPermissions($permission))
            ->get(route('admin.payments.print', $this->payment))
            ->assertOk();
    }

    public static function printPermissions(): array
    {
        return [
            'payment-index' => ['payment-index'],
            'student-payment, redirected here after taking a payment' => ['student-payment'],
            'student-show, from the payment modal on a student page' => ['student-show'],
            'user-payment-list' => ['user-payment-list'],
        ];
    }

    public function test_the_receipt_does_not_print_without_any_of_those_permissions(): void
    {
        $this->withReceiptSettings();

        $this->actingAs($this->teacher())
            ->get(route('admin.payments.print', $this->payment))
            ->assertForbidden();
    }

    public function test_the_payments_list_shows_the_edit_button_only_with_payment_edit(): void
    {
        Livewire::actingAs($this->userWithPermissions('payment-index'))
            ->test(PaymentList::class)
            ->assertSeeHtml('openPayment('.$this->payment->id.')')
            ->assertSeeHtml(route('admin.payments.send-sms', $this->payment->id))
            ->assertDontSeeHtml(route('admin.payments.edit', $this->payment));

        Livewire::actingAs($this->userWithPermissions('payment-index', 'payment-edit'))
            ->test(PaymentList::class)
            ->assertSeeHtml(route('admin.payments.edit', $this->payment));
    }

    public function test_the_student_payments_tab_shows_edit_and_print_only_to_those_who_can_use_them(): void
    {
        // The tab reads the logo setting on mount.
        Settings::firstOrCreate(['key' => 'logo']);

        Livewire::actingAs($this->teacher())
            ->test(StudentPaymentTable::class, ['student' => $this->student])
            ->assertSeeHtml('openPayment('.$this->payment->id.')')
            ->assertDontSeeHtml(route('admin.payments.edit', $this->payment->id))
            ->assertDontSeeHtml(route('admin.payments.print', 0));

        Livewire::actingAs($this->userWithPermissions('payment-edit', 'student-payment'))
            ->test(StudentPaymentTable::class, ['student' => $this->student])
            ->assertSeeHtml(route('admin.payments.edit', $this->payment->id))
            ->assertSeeHtml(route('admin.payments.print', 0));
    }

    private function userWithPermissions(string ...$permissions): User
    {
        $role = Role::create(['name' => 'Test role '.uniqid(), 'guard_name' => 'web']);

        foreach ($permissions as $permission) {
            $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        return $this->staffWithRole($role);
    }

    private function teacher(): User
    {
        $teacher = $this->staffWithRole(Role::findOrCreate('Teacher', 'web'));

        // The seeded Teacher role holds none of these; if a database gives
        // it one, the forbidden assertions would not be testing this fix.
        $this->assertFalse($teacher->hasAnyPermission([
            'payment-index', 'payment-show', 'payment-edit', 'payment-refund',
            'student-payment', 'student-show', 'user-payment-list',
        ]));

        return $teacher;
    }

    private function staffWithRole(Role $role): User
    {
        $user = User::factory()->create(['current_branch_id' => $this->branch->id]);
        $user->branches()->attach($this->branch->id);
        $user->assignRole($role);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    /**
     * Without a receipt settings row, print builds one from the address and
     * phone settings, which a test database need not have.
     */
    private function withReceiptSettings(): void
    {
        if (! ReceiptSetting::query()->exists()) {
            ReceiptSetting::create(['header_text' => 'Test', 'show_logo' => false]);
        }
    }
}
