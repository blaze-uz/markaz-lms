<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Group;
use App\Models\Student;
use App\Observers\CourseObserver;
use App\Observers\GroupObserver;
use App\Observers\StudentObserver;
use App\Services\EskizSmsDriver;
use App\Services\LogSmsDriver;
use App\Services\SmsDriver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Permission\Middlewares\PermissionMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsDriver::class, fn () => match (config('sms.driver')) {
            'log' => new LogSmsDriver(),
            'eskiz' => new EskizSmsDriver(),
            default => throw new \InvalidArgumentException('Unknown SMS driver: '.config('sms.driver')),
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Student::observe(StudentObserver::class);
        Group::observe(GroupObserver::class);
        Course::observe(CourseObserver::class);

        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
        Livewire::addPersistentMiddleware([PermissionMiddleware::class]);
    }
}
