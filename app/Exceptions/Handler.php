<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->header('X-Livewire') && $e instanceof \Illuminate\Session\TokenMismatchException) {
            \Illuminate\Support\Facades\Log::error('LIVEWIRE X-REDIRECT PREVENTED DUE TO CSRF', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'CSRF Token Mismatch: ' . $e->getMessage()], 500);
        }

        $response = parent::render($request, $e);

        if ($request->header('X-Livewire') && $response->isRedirection()) {
            if (str_contains($response->getTargetUrl(), 'livewire/update')) {
                \Illuminate\Support\Facades\Log::error('LIVEWIRE X-REDIRECT CAUGHT', [
                    'exception_class' => get_class($e),
                    'exception_message' => $e->getMessage(),
                    'exception_trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Caught unexpected redirect to livewire/update',
                    'exception_class' => get_class($e),
                    'exception_message' => $e->getMessage()
                ], 500);
            }
        }

        return $response;
    }
}
