<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

/** Format respons API yang konsisten: {success, message, data, pagination?}. */
trait ApiResponses
{
    protected function ok(string $message, mixed $data = null, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function fail(string $message, int $status, array $extra = []): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message] + $extra, $status);
    }

    protected function page(string $message, LengthAwarePaginator $p, mixed $items): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'pagination' => [
                'current_page' => $p->currentPage(), 'per_page' => $p->perPage(), 'total' => $p->total(),
                'last_page' => $p->lastPage(), 'from' => $p->firstItem(), 'to' => $p->lastItem(),
            ],
        ], Response::HTTP_OK);
    }
}
