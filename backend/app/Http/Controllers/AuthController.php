<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_demo' => false,
        ]);

        return response()->json([
            'token' => $user->createToken('web-app')->plainTextToken,
            'user' => ['name' => $user->name, 'email' => $user->email, 'is_demo' => false],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $user->tokens()->where('name', 'web-app')->delete();

        return response()->json([
            'token' => $user->createToken('web-app')->plainTextToken,
            'user' => ['name' => $user->name, 'email' => $user->email, 'is_demo' => $user->is_demo],
        ]);
    }

    public function demo(): JsonResponse
    {
        $user = User::query()->where('is_demo', true)->firstOrFail();
        $user->tokens()
            ->where('name', 'demo-web-app')
            ->where('expires_at', '<', now())
            ->delete();

        return response()->json([
            'token' => $user->createToken('demo-web-app', ['*'], now()->addHours(4))->plainTextToken,
            'user' => ['name' => $user->name, 'email' => $user->email, 'is_demo' => true],
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'is_demo' => $request->user()->is_demo,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([], 204);
    }
}
