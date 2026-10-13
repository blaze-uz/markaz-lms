<?php

namespace Tests\Unit;

use App\Http\Controllers\StaffTelegramBotController;
use App\Services\StaffTelegramBotService;
use Illuminate\Http\Request;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * The staff settings page used to load a fixed staff account whenever the
 * request carried no telegram_id, and before that whichever account the
 * telegram_id named. It now shows only the staff member AuthenticateStaffWebApp
 * verified through Telegram; without one it is refused, whatever the request says.
 */
class StaffTelegramBotSettingsTest extends TestCase
{
    /**
     * @dataProvider unverifiedRequests
     */
    public function test_settings_without_a_verified_staff_member_is_not_found(array $query): void
    {
        $service = Mockery::mock(StaffTelegramBotService::class);
        $service->shouldNotReceive('getUserByTelegramId');

        $controller = new StaffTelegramBotController($service);

        try {
            $controller->settings(Request::create('/inertia-teachers/settings', 'GET', $query));
            $this->fail('Expected the settings page to be refused.');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    public static function unverifiedRequests(): array
    {
        return [
            'no parameter' => [[]],
            'empty string' => [['telegram_id' => '']],
            'a telegram_id nobody verified' => [['telegram_id' => '555']],
        ];
    }
}
