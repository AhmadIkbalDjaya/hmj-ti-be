<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Authentication\LoginRequest;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    use HttpResponses;

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (Auth::attempt($validated)) {
            $user = Auth::user();
            $data = [
                'token' => $user->createToken('token')->plainTextToken,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                ],
            ];

            return $this->respondSuccess($data, 'Login Success');
        }

        return $this->respondBadRequest('Username atau Password salah');
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->respondSuccess(null, 'Logout Berhasil');
        } catch (\Throwable $th) {
            return $this->respondServerError($th, 'Logout gagal');
        }
    }
}
