<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthenticateStudentWebApp;
use App\Models\Group;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Settings;
use App\Models\Student;
use App\Models\User;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The student Telegram mini-app (/twa) loaded whichever student a telegram_id
 * query parameter named. Now the mini-app posts Telegram's signed initData
 * once, verified with the student bot's token, and the server keeps only the
 * Telegram id it verified.
 */
class StudentWebAppAuthTest extends TestCase
{
    use DatabaseTransactions;

    private const TOKEN = '123456:students-bot-token';

    private const STAFF_TOKEN = '654321:staff-bot-token';

    private const PAGES = ['/twa/dashboard', '/twa/attendance', '/twa/payments', '/twa/settings'];

    private TelegraphBot $bot;

    private Student $student;

    private Student $otherStudent;

    private Group $group;

    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        TelegraphBot::whereIn('name', ['Students app', 'Staff app'])->delete();
        $this->bot = TelegraphBot::create(['name' => 'Students app', 'token' => self::TOKEN]);
        $staffBot = TelegraphBot::create(['name' => 'Staff app', 'token' => self::STAFF_TOKEN]);

        $teacher = User::create([
            'name' => 'Ustoz',
            'email' => 'student-webapp-'.uniqid().'@example.test',
            'password' => bcrypt('secret'),
        ]);
        $this->group = Group::create([
            'name' => 'Azizaning guruhi',
            'course_id' => 0,
            'start_date' => now()->toDateString(),
            'teacher_id' => $teacher->id,
            'price' => 100000,
            'state' => 'active',
        ]);

        $this->student = $this->makeStudent('800001', 'Aziza', 'Karimova', $teacher);
        $this->otherStudent = $this->makeStudent('800002', 'Bobur', 'Toshev', $teacher);
        $this->group->students()->attach($this->student->id);

        $this->makeChat($this->bot, '800001', 'Aziza Karimova');
        $this->makeChat($this->bot, '800002', 'Bobur Toshev');
        // The same person may also chat with the staff bot.
        $this->makeChat($staffBot, '800001', 'Staff chat');

        $type = PaymentType::create(['name' => 'Student web app cash '.uniqid()]);
        $this->payment = $this->makePayment($this->student, $type, $teacher, 'Azizaning tolovi');
        $this->makePayment($this->otherStudent, $type, $teacher, 'Boburning tolovi');
    }

    public function test_a_telegram_id_alone_no_longer_opens_a_students_pages(): void
    {
        foreach (self::PAGES as $page) {
            $this->get($page.'?telegram_id=800001')
                ->assertOk()
                ->assertDontSee('Karimova')
                ->assertDontSee('Azizaning')
                ->assertInertia(fn (Assert $p) => $p
                    ->component('Students/Authenticate', false)
                    ->where('next', $page.'?telegram_id=800001')
                    ->where('error', null)
                    ->missing('student'));
        }
    }

    public function test_the_profile_cannot_be_changed_without_a_verified_session(): void
    {
        $this->patch('/twa/settings/profile', ['display_name' => 'Hacked', 'telegram_id' => '800001'])
            ->assertUnauthorized();

        $this->assertSame('Aziza Karimova', $this->chat($this->bot, '800001')->display_name);
    }

    public function test_the_bots_open_app_button_still_leads_into_the_app(): void
    {
        // Buttons already sent in chats point at /twa/login/{id}?next=/twa/dashboard.
        $this->get('/twa/login/800001?next=/twa/dashboard')
            ->assertRedirect('/twa/dashboard?telegram_id=800001');
        $this->get('/twa/login/800001')
            ->assertRedirect('/twa/dashboard?telegram_id=800001');
        $this->get('/twa/login/800001?next='.urlencode('/twa/attendance?group_id=5'))
            ->assertRedirect('/twa/attendance?group_id=5&telegram_id=800001');
    }

    public function test_the_login_link_never_redirects_out_of_the_mini_app(): void
    {
        foreach (['https://evil.example/twa/dashboard', '//evil.example/twa', '/admin'] as $next) {
            $this->get('/twa/login/800001?next='.urlencode($next))
                ->assertRedirect('/twa/dashboard?telegram_id=800001');
        }
    }

    public function test_valid_initdata_signs_the_student_in_and_the_app_works(): void
    {
        $this->post('/twa/auth', [
            'init_data' => $this->initData(800001),
            'next' => '/twa/dashboard?telegram_id=800001',
        ])->assertRedirect('/twa/dashboard?telegram_id=800001');

        $this->assertSame('800001', session(AuthenticateStudentWebApp::SESSION_KEY));

        $this->get('/twa/dashboard?telegram_id=800001')
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Students/Index', false)
                ->where('student.id', $this->student->id)
                ->has('groups', 1)
                ->where('groups.0.id', $this->group->id));

        $this->get('/twa/attendance')
            ->assertInertia(fn (Assert $p) => $p
                ->component('Students/Attendance', false)
                ->where('student.id', $this->student->id)
                ->where('selectedGroupId', $this->group->id));

        $this->get('/twa/payments')
            ->assertDontSee('Boburning')
            ->assertInertia(fn (Assert $p) => $p
                ->component('Students/Payments', false)
                ->has('payments', 1)
                ->where('payments.0.id', $this->payment->id));

        $this->get('/twa/settings')
            ->assertInertia(fn (Assert $p) => $p
                ->component('Students/Settings', false)
                ->where('student.id', $this->student->id)
                ->where('telegraphChat.chat_id', '800001')
                ->where('telegraphChat.telegraph_bot_id', $this->bot->id));
    }

    public function test_a_student_renames_only_their_own_chat_with_the_student_bot(): void
    {
        $this->signIn('800001');

        $this->patch('/twa/settings/profile', ['display_name' => 'Aziza K.'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('Aziza K.', $this->chat($this->bot, '800001')->display_name);
        $this->assertSame('Bobur Toshev', $this->chat($this->bot, '800002')->display_name);
        $this->assertSame('Staff chat', TelegraphChat::where('chat_id', '800001')
            ->where('telegraph_bot_id', '!=', $this->bot->id)->value('display_name'));
    }

    public function test_missing_initdata_is_refused(): void
    {
        $this->post('/twa/auth', ['next' => '/twa/dashboard'])
            ->assertForbidden()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Students/Authenticate', false)
                ->where('error', fn ($error) => filled($error)));

        $this->assertNull(session(AuthenticateStudentWebApp::SESSION_KEY));
    }

    public function test_forged_initdata_is_refused(): void
    {
        // Signed by Telegram, but for the staff bot: not proof for the student bot.
        $signedForTheStaffBot = $this->initData(800001, self::STAFF_TOKEN);
        $userSwapped = str_replace(
            rawurlencode($this->telegramUser(800002)),
            rawurlencode($this->telegramUser(800001)),
            $this->initData(800002),
        );

        foreach ([$signedForTheStaffBot, $userSwapped, 'user=%7B%22id%22%3A800001%7D&hash=deadbeef'] as $forged) {
            $this->post('/twa/auth', ['init_data' => $forged, 'next' => '/twa/dashboard'])
                ->assertForbidden();
        }

        $this->assertNull(session(AuthenticateStudentWebApp::SESSION_KEY));
    }

    public function test_stale_initdata_is_refused(): void
    {
        $this->post('/twa/auth', [
            'init_data' => $this->initData(800001, authDate: now()->subDays(2)->getTimestamp()),
            'next' => '/twa/dashboard',
        ])->assertForbidden();

        $this->assertNull(session(AuthenticateStudentWebApp::SESSION_KEY));
    }

    public function test_initdata_of_one_student_cannot_open_a_link_naming_another(): void
    {
        $this->post('/twa/auth', [
            'init_data' => $this->initData(800001),
            'next' => '/twa/payments?telegram_id=800002',
        ])->assertForbidden();

        $this->assertNull(session(AuthenticateStudentWebApp::SESSION_KEY));
    }

    public function test_a_verified_session_cannot_be_pointed_at_another_student(): void
    {
        $this->signIn('800001');

        // A page view asks Telegram again instead of showing anyone's data...
        foreach (self::PAGES as $page) {
            $this->get($page.'?telegram_id=800002')
                ->assertOk()
                ->assertDontSee('Toshev')
                ->assertDontSee('Karimova')
                ->assertInertia(fn (Assert $p) => $p->component('Students/Authenticate', false));
        }

        // ...and a write is refused outright.
        $this->patch('/twa/settings/profile', ['display_name' => 'Hacked', 'telegram_id' => '800002'])
            ->assertForbidden();

        $this->assertSame('Bobur Toshev', $this->chat($this->bot, '800002')->display_name);
        $this->assertSame('Aziza Karimova', $this->chat($this->bot, '800001')->display_name);
    }

    public function test_a_telegram_user_not_bound_to_a_student_is_refused(): void
    {
        $this->post('/twa/auth', [
            'init_data' => $this->initData(899999),
            'next' => '/twa/dashboard',
        ])->assertForbidden();

        $this->assertNull(session(AuthenticateStudentWebApp::SESSION_KEY));
    }

    public function test_the_token_saved_in_settings_is_used_when_the_bot_row_is_missing(): void
    {
        $this->bot->delete();
        Settings::updateOrCreate(['key' => 'student_telegram_bot_token'], ['value' => self::TOKEN]);

        $this->post('/twa/auth', [
            'init_data' => $this->initData(800001),
            'next' => '/twa/dashboard',
        ])->assertRedirect('/twa/dashboard');

        $this->assertSame('800001', session(AuthenticateStudentWebApp::SESSION_KEY));
    }

    public function test_nothing_is_accepted_while_the_student_bot_token_is_not_set(): void
    {
        $this->bot->delete();
        Settings::where('key', 'student_telegram_bot_token')->delete();

        $this->post('/twa/auth', [
            'init_data' => $this->initData(800001),
            'next' => '/twa/dashboard',
        ])->assertForbidden();

        $this->assertNull(session(AuthenticateStudentWebApp::SESSION_KEY));
    }

    public function test_signing_in_never_redirects_out_of_the_mini_app(): void
    {
        foreach (['https://evil.example/twa/dashboard', '//evil.example/twa', '/inertia-teachers'] as $next) {
            $this->post('/twa/auth', [
                'init_data' => $this->initData(800001),
                'next' => $next,
            ])->assertRedirect('/twa/dashboard');
        }
    }

    private function signIn(string $telegramId): void
    {
        $this->withSession([AuthenticateStudentWebApp::SESSION_KEY => $telegramId]);
    }

    private function telegramUser(int $id): string
    {
        return json_encode(['id' => $id, 'first_name' => 'Talaba']);
    }

    /**
     * initData as Telegram builds it for the bot holding $token.
     */
    private function initData(int $telegramId, string $token = self::TOKEN, ?int $authDate = null): string
    {
        $fields = [
            'auth_date' => $authDate ?? now()->getTimestamp(),
            'query_id' => 'AAHdF6IQAAAAAN0XohDhrOrc',
            'user' => $this->telegramUser($telegramId),
        ];
        ksort($fields);

        $dataCheckString = implode("\n", array_map(fn ($k, $v) => "{$k}={$v}", array_keys($fields), $fields));
        $fields['hash'] = hash_hmac('sha256', $dataCheckString, hash_hmac('sha256', $token, 'WebAppData', true));

        return http_build_query($fields, '', '&', PHP_QUERY_RFC3986);
    }

    private function makeStudent(string $telegramId, string $name, string $surname, User $creator): Student
    {
        Student::where('telegram_chat_id', $telegramId)->update(['telegram_chat_id' => null]);

        // Quietly: Student's creating hook stamps auth()->id(), and nobody is signed in here.
        $student = new Student([
            'name' => $name,
            'surname' => $surname,
            'phone' => '90'.random_int(1000000, 9999999),
            'telegram_chat_id' => $telegramId,
            'created_by_id' => $creator->id,
        ]);
        $student->saveQuietly();

        return $student;
    }

    private function makeChat(TelegraphBot $bot, string $chatId, string $displayName): TelegraphChat
    {
        $chat = $bot->chats()->create(['chat_id' => $chatId, 'name' => $displayName]);
        $chat->forceFill(['display_name' => $displayName, 'state' => 'registered'])->save();

        return $chat;
    }

    private function chat(TelegraphBot $bot, string $chatId): TelegraphChat
    {
        return TelegraphChat::where('chat_id', $chatId)->where('telegraph_bot_id', $bot->id)->firstOrFail();
    }

    private function makePayment(Student $student, PaymentType $type, User $creator, string $comment): Payment
    {
        return Payment::create([
            'student_id' => $student->id,
            'payment_type_id' => $type->id,
            'amount' => 50000,
            'created_by' => $creator->id,
            'date' => now(),
            'comment' => $comment,
        ]);
    }
}
