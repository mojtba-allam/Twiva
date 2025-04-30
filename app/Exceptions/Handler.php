<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Route not found.',
                    'status' => 404
                ], 404);
            }
            
            // For web routes, return the 404 view
            return response()->view('errors.404', [], 404);
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Unauthenticated.',
                        'status' => 401
                    ], 401);
                }
                
                // If it's an API request but doesn't expect JSON (like browser request)
                if ($request->is('api/admins/*')) {
                    return redirect()->route('admin.login');
                }
            }
            
            return redirect()->guest(route('admin.login'));
        });
    }
}
