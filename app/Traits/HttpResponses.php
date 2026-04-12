<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

trait HttpResponses
{
    protected function respondSuccess(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        $payload = ['message' => $message];

        if (! is_null($data)) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    protected function respondCreated(mixed $data = null, string $message = 'Created Successfully'): JsonResponse
    {
        return $this->respondSuccess($data, $message, 201);
    }

    protected function respondNotFound(string $message = 'Resource Not Found'): JsonResponse
    {
        return response()->json(['message' => $message], 404);
    }

    protected function respondUnauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return response()->json(['message' => $message], 401);
    }

    protected function respondForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return response()->json(['message' => $message], 403);
    }

    protected function respondUnprocessable(mixed $errors = null, string $message = 'Unprocessable Entity'): JsonResponse
    {
        $payload = ['message' => $message];

        if (! is_null($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, 422);
    }

    protected function respondBadRequest(string $message = 'Bad Request'): JsonResponse
    {
        return response()->json(['message' => $message], 400);
    }

    protected function respondServerError(Throwable $th, string $message = 'Internal Server Error'): JsonResponse
    {
        Log::error($th);

        return response()->json(['message' => $message], 500);
    }

    protected function respondSuccessWithMeta(mixed $data = null, mixed $meta = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        $payload = ['message' => $message];

        if (! is_null($data)) {
            $payload['data'] = $data;
        }

        if (! is_null($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }
}
