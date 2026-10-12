<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    protected function successResponse(
        mixed $data = null,
        string $message = '요청이 성공적으로 처리되었습니다.',
        int $status = Response::HTTP_OK,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return \response()->json($response, $status);
    }

    protected function createResponse(
        mixed $data,
        string $message = '리소스가 성공적으로 생성되었습니다.',
    ): JsonResponse {
        return $this->successResponse(
            data: $data,
            message: $message,
            status: Response::HTTP_CREATED
        );
    }

    protected function errorResponse(
        string $message = '요청 처리 중 에러가 발생했습니다.',
        int $status = Response::HTTP_BAD_REQUEST,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return \response()->json($response, $status);
    }

    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        mixed $data,
        string $message = '데이터 목록을 성공적으로 조회했습니다.'
    ): JsonResponse {
        return $this->successResponse(
            data: $data,
            message: $message,
            meta: [
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'has_more' => $paginator->hasMorePages(),
                ],
            ]
        );
    }

    protected function noContentResponse(): JsonResponse
    {
        return \response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
