<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Sentry\Laravel\Integration;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            event(new \App\Events\ErrorOccurred($e));
            if(config('sentry.dsn')) {
                Integration::captureUnhandledException($e);
            }
        });

        $this->renderable(function (HttpException $exception, $request) {
            if ($exception->getStatusCode() === 419) {
                return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
            }
        });
    }
}
