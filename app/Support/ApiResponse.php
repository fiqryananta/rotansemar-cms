<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success(string $message = 'OK', mixed $data = null, array $meta = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => (object) $meta,
        ];
    }

    public static function error(string $message, array $errors = [], int $code = 422): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => (object) $errors,
            'code' => $code,
        ];
    }

    public static function paginated(string $message, LengthAwarePaginator $paginator): array
    {
        return self::success($message, $paginator->items(), [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ]);
    }
}
