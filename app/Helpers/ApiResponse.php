<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        mixed  $data = null,
        string $message = 'OK',
        array  $meta = [],
        int    $status = 200
    ): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data ?? (object)[],
            'meta' => (object)$meta,
        ], $status);
    }

    public static function error(
        string $code,
        string $message,
        mixed  $details = null,
        int    $status = 400
    ): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => [
                'code' => $code,
                'details' => $details ?? (object)[],
            ],
        ], $status);
    }
}
