<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApiResponse;
use App\Support\MobileAccessScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::query()
            ->with(['opd:id,name', 'puskesmas:id,name', 'kecamatan:id,name', 'kelurahan:id,name'])
            ->where('email', $validated['email'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(ApiResponse::error('Email atau password tidak valid.'), 401);
        }

        $tokenName = $validated['device_name'] ?? 'mobile-app';
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json(ApiResponse::success('Login berhasil.', [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUser($user),
            'roles' => MobileAccessScope::roles($user),
            'access' => MobileAccessScope::accessMatrix($user),
        ]));
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json(ApiResponse::success('Logout berhasil.'));
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load(['opd:id,name', 'puskesmas:id,name', 'kecamatan:id,name', 'kelurahan:id,name']);

        return response()->json(ApiResponse::success('Profil user.', [
            'user' => $this->formatUser($user),
            'roles' => MobileAccessScope::roles($user),
            'access' => MobileAccessScope::accessMatrix($user),
        ]));
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'unit' => [
                'opd' => $user->opd?->name,
                'puskesmas' => $user->puskesmas?->name,
                'kecamatan' => $user->kecamatan?->name,
                'kelurahan' => $user->kelurahan?->name,
            ],
        ];
    }
}
