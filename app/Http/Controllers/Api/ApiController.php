<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    protected function respond(mixed $data, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => $meta,
        ], $status);
    }

    /**
     * @param class-string<\Illuminate\Http\Resources\Json\JsonResource> $resource
     */
    protected function respondWithPagination(LengthAwarePaginator $paginator, string $resource): JsonResponse
    {
        $collection = $resource::collection($paginator->getCollection())->resolve();

        return $this->respond($collection, Pagination::meta($paginator));
    }
}
