<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::make([], collect($validator->errors()->messages()), [], 422)->response();
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return ApiResponse::make(data: [
            'user_id'    => $user->id,
            'token'      => $token,
            'token_type' => 'Bearer',
        ], statusCode: 201)->response();
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::make([], collect($validator->errors()->messages()), [], 422)->response();
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return ApiResponse::make(statusCode: 401)->setError('auth', 'auth_failed', 'try auth failed')->response();
        }

        $user  = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return ApiResponse::make([
            'user_id'    => $user->id,
            'token'      => $token,
            'token_type' => 'Bearer',
        ])->response();
    }
}
