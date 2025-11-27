<?php

namespace App\Http\Responses;

use App\Support\Traits\Makeable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ApiResponse
{
    use Makeable;

    private array|Model|JsonResource $data;
    private array $errors;
    private array $meta;
    private int $statusCode;
    private array $headers = [];

    public function __construct(array|Model|JsonResource $data = [], Collection $errors = null, array $meta = [], int $statusCode = 200)
    {
        $this->data       = $data;
        $this->errors     = $this->_prepareErrors($errors ?? collect());
        $this->meta       = $meta;
        $this->statusCode = $statusCode;
    }

    public function setPagination(int $limit, int $page, int $total): ApiResponse
    {
        $this->meta['pagination']['limit'] = $limit;
        $this->meta['pagination']['page']  = $page;
        $this->meta['pagination']['total'] = $total;

        return $this;
    }

    public function setCursor($cursor): ApiResponse
    {
        $this->meta['pagination']['cursor'] = $cursor;
        return $this;
    }

    public function setHeaders(array $headers): ApiResponse
    {
        $this->headers = $headers;
        return $this;
    }

    public function setError(string $key, string $code, string $message): ApiResponse
    {
        $this->errors[$key] = [
            'code'    => $code,
            'message' => $message,
        ];
        return $this;
    }

    public function response(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data'   => $this->data,
            'errors' => $this->errors,
            'meta'   => $this->meta,
        ], $this->statusCode)->withHeaders($this->headers);
    }

    private function _prepareErrors(Collection $errors): array
    {
        return $errors->map(function ($messages) {
            return [
                'code'    => 'validation_error',
                'message' => $messages[0],
            ];
        })->all();
    }
}
