<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthenticateStaffWebApp;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The staff Telegram mini-app (inertia-teachers) had no authentication: the
 * staff member was whoever the telegram_id parameter named, and the group and
 * attendance pages needed no id at all. Now the mini-app posts Telegram's
 * signed initData once, the server keeps only the Telegram id it verified, and
 * a staff member reaches only their own groups.
 */
class StaffWebAppAuthTest extends TestCase
{
    use DatabaseTransactions;

    private const TOKEN = '123456:staff-bot-token';

    private User $teacher;

    private User $otherTeacher;

    private Group $group;

    private Group $otherGroup;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        TelegraphBot::where('name', 'Staff app')->delete();
        TelegraphBot::create(['name' => 'Staff app', 'token' => self::TOKEN]);

        $this->branch = Branch::create(['name' => 'Staff web app branch '.uniqid()]);
        $this->teacher = $this->makeTeacher('700001', 'Ustoz Birinchi');
        $this->otherTeacher = $this->makeTeacher('700002', 'Ustoz Ikkinchi');
        $this->group = $this->makeGroup($this->teacher, 'Birinchining guruhi');
        $this->otherGroup = $this->makeGroup($this->otherTeacher, 'Ikkinchining guruhi');
    }

    public function test_a_telegram_id_alone_no_longer_opens_a_staff_members_page(): void
    {
        foreach (['/inertia-teachers', '/inertia-teachers/groups', '/inertia-teachers/settings'] as $page) {
            $this->get($page.'?telegram_id=700001')
                ->assertOk()
                ->assertDontSee('Ustoz Birinchi')
                ->assertInertia(fn (Assert $p) => $p
                    ->component('Staff/Authenticate', false)
                    ->where('next', $page.'?telegram_id=700001')
                    ->where('error', null));
        }
    }

    public function test_group_pages_and_attendance_need_a_verified_session_too(): void
    {
        $this->get("/inertia-teachers/groups/{$this->group->id}")
            ->assertOk()
            ->assertDontSee('Birinchining guruhi')
            ->assertInertia(fn (Assert $p) => $p->component('Staff/Authenticate', false));

        $this->post("/inertia-teachers/groups/{$this->group->id}/attendance", $this->attendance())
            ->assertUnauthorized();
        $this->put("/inertia-teachers/groups/{$this->group->id}/attendance", $this->attendance())
            ->assertUnauthorized();

        $this->assertSame(0, Attendance::where('group_id', $this->group->id)->count());
    }

    public function test_valid_initdata_signs_the_staff_member_in_and_the_app_works(): void
    {
        $this->post('/inertia-teachers/auth', [
            'init_data' => $this->initData(700001),
            'next' => '/inertia-teachers?telegram_id=700001',
        ])->assertRedirect('/inertia-teachers?telegram_id=700001');

        $this->assertSame('700001', session(AuthenticateStaffWebApp::SESSION_KEY));

        $this->get('/inertia-teachers')
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Staff/Index', false)
                ->where('user.id', $this->teacher->id));

        $this->get('/inertia-teachers/groups')
            ->assertInertia(fn (Assert $p) => $p
                ->component('Staff/Groups', false)
                ->has('groups', 1)
                ->where('groups.0.id', $this->group->id));

        $this->get("/inertia-teachers/groups/{$this->group->id}")
            ->assertInertia(fn (Assert $p) => $p
                ->component('Staff/Group', false)
                ->where('group.id', $this->group->id));
    }

    public function test_a_staff_member_can_still_take_attendance_for_their_own_group(): void
    {
        $student = $this->makeStudent($this->group);
        $this->signIn('700001');

        $this->post("/inertia-teachers/groups/{$this->group->id}/attendance", $this->attendance($student->id))
            ->assertRedirect(route('inertia-teachers.index'));

        $this->assertTrue(Attendance::where('group_id', $this->group->id)
            ->where('student_id', $student->id)
            ->where('status', 'present')
            ->exists());
    }

    public function test_missing_initdata_is_refused(): void
    {
        $this->post('/inertia-teachers/auth', ['next' => '/inertia-teachers'])
            ->assertForbidden()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Staff/Authenticate', false)
                ->where('error', fn ($error) => filled($error)));

        $this->assertNull(session(AuthenticateStaffWebApp::SESSION_KEY));
    }

    public function test_forged_initdata_is_refused(): void
    {
        $signedForAnotherBot = $this->initData(700001, 'another:bot-token');
        $userSwapped = str_replace(
            rawurlencode($this->telegramUser(700002)),
            rawurlencode($this->telegramUser(700001)),
            $this->initData(700002),
        );

        foreach ([$signedForAnotherBot, $userSwapped, 'user=%7B%22id%22%3A700001%7D&hash=deadbeef'] as $forged) {
            $this->post('/inertia-teachers/auth', ['init_data' => $forged, 'next' => '/inertia-teachers'])
                ->assertForbidden();
        }

        $this->assertNull(session(AuthenticateStaffWebApp::SESSION_KEY));
    }

    public function test_stale_initdata_is_refused(): void
    {
        $this->post('/inertia-teachers/auth', [
            'init_data' => $this->initData(700001, authDate: now()->subDays(2)->getTimestamp()),
            'next' => '/inertia-teachers',
        ])->assertForbidden();

        $this->assertNull(session(AuthenticateStaffWebApp::SESSION_KEY));
    }

    public function test_initdata_of_one_staff_member_cannot_open_a_link_naming_another(): void
    {
        $this->post('/inertia-teachers/auth', [
            'init_data' => $this->initData(700001),
            'next' => '/inertia-teachers?telegram_id=700002',
        ])->assertForbidden();

        $this->assertNull(session(AuthenticateStaffWebApp::SESSION_KEY));
    }

    public function test_a_verified_session_cannot_be_pointed_at_another_staff_member(): void
    {
        $this->signIn('700001');

        // A page view asks Telegram again instead of showing anyone's data...
        $this->get('/inertia-teachers?telegram_id=700002')
            ->assertOk()
            ->assertDontSee('Ustoz Ikkinchi')
            ->assertDontSee('Ustoz Birinchi')
            ->assertInertia(fn (Assert $p) => $p->component('Staff/Authenticate', false));

        // ...and a write is refused outright.
        $this->put("/inertia-teachers/groups/{$this->group->id}/attendance?telegram_id=700002", $this->attendance())
            ->assertForbidden();
    }

    public function test_a_staff_member_cannot_reach_another_teachers_group(): void
    {
        $student = $this->makeStudent($this->otherGroup);
        $this->signIn('700001');

        $this->get("/inertia-teachers/groups/{$this->otherGroup->id}")->assertNotFound();
        $this->get("/inertia-teachers/groups/{$this->otherGroup->id}/attendance")->assertNotFound();
        $this->get("/inertia-teachers/groups/{$this->otherGroup->id}/attendance/edit")->assertNotFound();
        $this->post("/inertia-teachers/groups/{$this->otherGroup->id}/attendance", $this->attendance($student->id))->assertNotFound();
        $this->put("/inertia-teachers/groups/{$this->otherGroup->id}/attendance", $this->attendance($student->id))->assertNotFound();

        $this->assertSame(0, Attendance::where('group_id', $this->otherGroup->id)->count());
    }

    public function test_a_telegram_user_who_is_not_staff_is_refused(): void
    {
        $this->post('/inertia-teachers/auth', [
            'init_data' => $this->initData(799999),
            'next' => '/inertia-teachers',
        ])->assertForbidden();

        $this->assertNull(session(AuthenticateStaffWebApp::SESSION_KEY));
    }

    public function test_nothing_is_accepted_while_the_staff_bot_token_is_not_set(): void
    {
        TelegraphBot::where('name', 'Staff app')->delete();

        $this->post('/inertia-teachers/auth', [
            'init_data' => $this->initData(700001),
            'next' => '/inertia-teachers',
        ])->assertForbidden();
    }

    public function test_signing_in_never_redirects_out_of_the_mini_app(): void
    {
        $this->post('/inertia-teachers/auth', [
            'init_data' => $this->initData(700001),
            'next' => 'https://evil.example/inertia-teachers',
        ])->assertRedirect('/inertia-teachers');
    }

    private function signIn(string $telegramId): void
    {
        $this->withSession([AuthenticateStaffWebApp::SESSION_KEY => $telegramId]);
    }

    private function attendance(?int $studentId = null): array
    {
        return ['status' => [($studentId ?? 1) => 'present'], 'homework' => [($studentId ?? 1) => false]];
    }

    private function telegramUser(int $id): string
    {
        return json_encode(['id' => $id, 'first_name' => 'Ustoz']);
    }

    /**
     * initData as Telegram builds it for the staff bot.
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

    private function makeTeacher(string $telegramId, string $name): User
    {
        User::where('telegram_id', $telegramId)->update(['telegram_id' => null]);

        $teacher = User::create([
            'name' => $name,
            'email' => 'staff-webapp-'.uniqid().'@example.test',
            'password' => bcrypt('secret'),
            'telegram_id' => $telegramId,
        ]);
        $teacher->branches()->attach($this->branch->id);

        return $teacher;
    }

    private function makeGroup(User $teacher, string $name): Group
    {
        $group = Group::create([
            'name' => $name,
            'course_id' => 0,
            'start_date' => now()->toDateString(),
            'teacher_id' => $teacher->id,
            'price' => 100000,
            'state' => 'active',
        ]);
        $group->branches()->attach($this->branch->id);

        return $group;
    }

    private function makeStudent(Group $group): Student
    {
        // Student's creating hook stamps auth()->id(), and nobody is signed in to the admin here.
        $student = Student::withoutEvents(fn () => Student::create([
            'name' => 'Talaba',
            'surname' => 'Sinov',
            'phone' => '+998901234567',
            'created_by_id' => $group->teacher_id,
        ]));
        $group->students()->attach($student->id);

        return $student;
    }
}
