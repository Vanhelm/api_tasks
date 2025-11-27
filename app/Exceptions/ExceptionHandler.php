<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class ExceptionHandler extends Handler
{
    public function render($request, \Throwable $e): \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($e instanceof AuthenticationException) {
            return ApiResponse::make(
                statusCode: 401
            )->setError('auth', 'auth_failed', 'try auth failed')->response();
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            return ApiResponse::make(
                statusCode: 405
            )->setError('method', 'method_not_allowed', $e->getMessage())->response();
        }

        return ApiResponse::make(
            statusCode: 500
        )->setError('core', 'internal_error', (env('APP_DEBUG') === true ? $e->getMessage() : 'internal_error'))->response();
    }
}
