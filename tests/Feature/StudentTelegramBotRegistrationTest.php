<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The student bot binds a Telegram account to a student by phone number, and
 * the mini-app then trusts that binding. A typed number or someone else's
 * contact card used to be enough, so anyone who knew a student's or parent's
 * phone could bind their own Telegram to that student. Only the sender's own
 * contact, shared with the button, binds now.
 */
class StudentTelegramBotRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    private const TOKEN = '123456:students-bot-token';

    private const SENDER = 900001;

    private TelegraphBot $bot;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        Telegraph::fake();

        TelegraphBot::where('name', 'Students app')->delete();
        $this->bot = TelegraphBot::create(['name' => 'Students app', 'token' => self::TOKEN]);

        $creator = User::create([
            'name' => 'Admin',
            'email' => 'student-bot-'.uniqid().'@example.test',
            'password' => bcrypt('secret'),
        ]);
        $phone = '90'.random_int(1000000, 9999999);
        Student::where('telegram_chat_id', (string) self::SENDER)->update(['telegram_chat_id' => null]);

        // Quietly: Student's creating hook stamps auth()->id(), and nobody is signed in here.
        $this->student = new Student([
            'name' => 'Aziza',
            'surname' => 'Karimova',
            'phone' => $phone,
            'created_by_id' => $creator->id,
        ]);
        $this->student->saveQuietly();

        $chat = $this->bot->chats()->create(['chat_id' => (string) self::SENDER, 'name' => 'Aziza']);
        $chat->forceFill(['state' => 'phone_request'])->save();
    }

    public function test_the_senders_own_contact_binds_the_student(): void
    {
        $this->sendMessage(['contact' => $this->contact(self::SENDER)]);

        $this->assertSame((string) self::SENDER, $this->student->fresh()->telegram_chat_id);
        Telegraph::assertSent("Muvaffaqiyatli ro'yxatdan o'tdingiz", false);
    }

    public function test_a_typed_phone_number_no_longer_binds_the_student(): void
    {
        $this->sendMessage(['text' => '+998'.$this->student->phone]);

        $this->assertNull($this->student->fresh()->telegram_chat_id);
        Telegraph::assertSent('tugmasini bosing', false);
    }

    public function test_someone_elses_contact_card_does_not_bind_the_student(): void
    {
        foreach ([self::SENDER + 1, null] as $owner) {
            $this->sendMessage(['contact' => $this->contact($owner)]);

            $this->assertNull($this->student->fresh()->telegram_chat_id);
        }

        Telegraph::assertSent("o'zingizning raqamingizni", false);
    }

    private function contact(?int $userId): array
    {
        return array_filter([
            'phone_number' => '+998'.$this->student->phone,
            'first_name' => 'Aziza',
            'user_id' => $userId,
        ], fn ($value) => $value !== null);
    }

    private function sendMessage(array $content): void
    {
        $this->postJson('/telegraph/'.self::TOKEN.'/webhook', [
            'update_id' => random_int(1, PHP_INT_MAX),
            'message' => [
                'message_id' => random_int(1, 1_000_000),
                'date' => now()->getTimestamp(),
                'from' => ['id' => self::SENDER, 'is_bot' => false, 'first_name' => 'Aziza'],
                'chat' => ['id' => self::SENDER, 'type' => 'private', 'first_name' => 'Aziza'],
                ...$content,
            ],
        ])->assertNoContent();
    }
}
