<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The mobile app's group routes answer only the group's teacher.
 *
 * GET groups/{group}/get-attendance read attendances by the id in the URL and
 * never asked whose group that was. Group ids run in sequence, so any teacher
 * signed in to the app could walk them and read every group's student names,
 * marks, comments and scores. It now looks the group up among the caller's own
 * groups first, as the routes next to it do, and a group that isn't the
 * caller's gets the same 404 they give.
 */
class MobileGroupOwnershipTest extends TestCase
{
    use DatabaseTransactions;

    private const DATE = '2026-09-14';

    public function test_only_the_groups_teacher_reads_its_attendance_for_a_day(): void
    {
        $fixture = $this->makeFixture();
        $uri = "/api/groups/{$fixture['group']->id}/get-attendance?date=".self::DATE;

        Sanctum::actingAs($fixture['other'], ['*']);

        $refused = $this->getJson($uri);

        $refused->assertNotFound()->assertJsonMissingPath('students');
        $this->assertRevealsNothingAboutTheStudent($refused, $fixture);

        Sanctum::actingAs($fixture['owner'], ['*']);

        $this->getJson($uri)
            ->assertOk()
            ->assertExactJson([
                'students' => [[
                    'student_id' => $fixture['student']->id,
                    'student_name' => $fixture['student']->getFullName(),
                    'status' => 'present',
                    'comment' => $fixture['attendance']->comment,
                    'score' => 5,
                ]],
            ]);
    }

    /**
     * The routes next to get-attendance already looked the group up among the
     * caller's own. Pinned so that none of them drifts the way it did.
     *
     * @return array<string, array{string, string}>
     */
    public static function otherGroupRoutes(): array
    {
        return [
            'group' => ['GET', '/api/groups/%d'],
            'card data' => ['GET', '/api/groups/%d/card-data'],
            'students' => ['GET', '/api/groups/%d/students'],
            'attendance history' => ['GET', '/api/groups/%d/attendance'],
            'store attendance' => ['POST', '/api/groups/%d/attendance'],
            'update attendance' => ['PUT', '/api/groups/%d/attendance-update'],
        ];
    }

    /**
     * @dataProvider otherGroupRoutes
     */
    public function test_the_other_group_routes_refuse_a_teacher_who_does_not_own_the_group(string $method, string $uri): void
    {
        $fixture = $this->makeFixture();

        Sanctum::actingAs($fixture['other'], ['*']);

        $payload = $method === 'GET' ? [] : [
            'date' => self::DATE,
            'students' => [[
                'id' => $fixture['student']->id,
                'status' => 'absent',
                'comment' => 'Overwritten by another teacher',
                'score' => 1,
            ]],
        ];

        $response = $this->json($method, sprintf($uri, $fixture['group']->id), $payload);

        $response->assertNotFound();
        $this->assertRevealsNothingAboutTheStudent($response, $fixture);

        // attendance-update deletes the day's marks before it writes: none of the owner's may move.
        $this->assertSame(
            [[$fixture['student']->id, 'present', $fixture['attendance']->comment, 5]],
            Attendance::where('group_id', $fixture['group']->id)->get()
                ->map(fn (Attendance $attendance) => [
                    $attendance->student_id,
                    $attendance->status->value,
                    $attendance->comment,
                    $attendance->score,
                ])
                ->all(),
        );
    }

    /**
     * @param  array{student: Student, attendance: Attendance}  $fixture
     */
    private function assertRevealsNothingAboutTheStudent(TestResponse $response, array $fixture): void
    {
        $body = $response->getContent();

        foreach ([
            $fixture['student']->name,
            $fixture['student']->surname,
            $fixture['student']->phone,
            $fixture['attendance']->comment,
        ] as $private) {
            $this->assertStringNotContainsString($private, $body);
        }
    }

    /**
     * Two teachers in one branch, and a group of the owner's with one student
     * marked present on self::DATE. Both teachers work in that branch on
     * purpose: the branch scope then lets the group through for either of
     * them, so only the ownership check stands between the other teacher and
     * the owner's students.
     *
     * @return array{owner: User, other: User, group: Group, student: Student, attendance: Attendance}
     */
    private function makeFixture(): array
    {
        $tag = Str::lower(Str::random(8));
        $branch = Branch::create(['name' => "Test branch {$tag}"]);

        $owner = $this->makeTeacher($branch);
        $other = $this->makeTeacher($branch);

        $group = Group::create([
            'name' => "Test group {$tag}",
            'course_id' => Course::create(['name' => "Test course {$tag}", 'duration' => 3])->id,
            'teacher_id' => $owner->id,
            'price' => 500000,
            'payment_type' => 'date_to_date',
            'start_date' => self::DATE,
            'state' => 'active',
        ]);
        $group->branches()->attach($branch->id);

        // Student stamps created_by_id, a NOT NULL column, from whoever is signed in.
        $this->actingAs($owner);
        $student = Student::create([
            'name' => "Madina-{$tag}",
            'surname' => "Karimova-{$tag}",
            'phone' => '998'.random_int(100000000, 999999999),
        ]);
        $student->branches()->attach($branch->id);
        $group->students()->attach($student->id, ['attached_at' => self::DATE]);

        $attendance = Attendance::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
            'date' => self::DATE,
            'status' => 'present',
            'comment' => "Homework not handed in {$tag}",
            'score' => 5,
        ]);

        return compact('owner', 'other', 'group', 'student', 'attendance');
    }

    private function makeTeacher(Branch $branch): User
    {
        $teacher = User::factory()->create(['current_branch_id' => $branch->id]);
        $teacher->branches()->attach($branch->id);
        $teacher->assignRole('Teacher');

        return $teacher;
    }
}
