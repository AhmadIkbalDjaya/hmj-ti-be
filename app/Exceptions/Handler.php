<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    // public function unauthenticated($request, AuthenticationException $exception)
    // {
    //     return response()->json([
    //         "code" => 401,
    //         "status" => "Unauthorized",
    //         "message" => "Unauthenticated",
    //         "errors" => "Unauthenticated",
    //     ], 401);
    // }

    // public function invalidJson($request, ValidationException $exception)
    // {
    //     return response()->json([
    //         "code" => $exception->status,
    //         "status" => "Unprocessable Content",
    //         "message" => "The given data wan invalid",
    //         "errors" => $exception->errors(),
    //     ], $exception->status);
    // }
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
        // $this->renderable(function (NotFoundHttpException $e, $request) {
        //     if ($request->wantsJson()) {
        //         return response()->json([
        //             "code" => "404",
        //             "status" => "Not Found",
        //             "message" => "Not Found",
        //             "errors" => "Not Found",
        //         ], 404);
        //     }
        // });
    }
}
