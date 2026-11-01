<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The paid theme's files are not in the repository, and an operator copies them
 * in from their own licence. A bare install must not request them, because each
 * request is a 404, and a licensed install must keep loading them.
 */
class ThemeAssetsTest extends TestCase
{
    use DatabaseTransactions;

    private const LAYOUT_FILES = [
        'assets/js/layout.js',
        'assets/css/bootstrap.min.css',
        'assets/css/icons.min.css',
        'assets/css/app.min.css',
        'assets/js/theme.js',
    ];

    private const PAGE_FILES = [
        'admin.profile.settings' => 'assets/js/profile-setting.init.js',
        'admin.settings.menu-settings-view' => 'assets/js/nestable.init.js',
    ];

    private string $publicPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publicPath = sys_get_temp_dir().'/markaz-lms-theme-'.uniqid();
        File::ensureDirectoryExists($this->publicPath);
        $this->app->usePublicPath($this->publicPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->publicPath);

        parent::tearDown();
    }

    public function test_a_bare_install_does_not_request_theme_files(): void
    {
        $layouts = $this->renderLayouts();

        foreach (self::LAYOUT_FILES as $file) {
            $this->assertStringNotContainsString($file, $layouts);
        }

        $admin = $this->admin();

        foreach (self::PAGE_FILES as $route => $file) {
            $this->actingAs($admin)->get(route($route))
                ->assertOk()
                ->assertDontSee($file);
        }
    }

    public function test_copied_theme_files_are_loaded(): void
    {
        foreach ([...self::LAYOUT_FILES, ...array_values(self::PAGE_FILES)] as $file) {
            File::ensureDirectoryExists(dirname(public_path($file)));
            File::put(public_path($file), '');
        }

        $layouts = $this->renderLayouts();

        foreach (self::LAYOUT_FILES as $file) {
            $this->assertStringContainsString(asset($file), $layouts);
        }

        $admin = $this->admin();

        foreach (self::PAGE_FILES as $route => $file) {
            $this->actingAs($admin)->get(route($route))
                ->assertOk()
                ->assertSee(asset($file));
        }
    }

    public function test_the_empty_list_icon_loads_from_the_lordicon_cdn_like_the_others(): void
    {
        $this->assertStringContainsString(
            'src="https://cdn.lordicon.com/msoeawqm.json"',
            Blade::render('<x-no-result />')
        );
    }

    private function renderLayouts(): string
    {
        return view('layouts.head-css')->render().view('layouts.vendor-scripts')->render();
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $admin->branches()->attach(Branch::create(['name' => 'Test branch '.uniqid()]));

        return $admin;
    }
}
