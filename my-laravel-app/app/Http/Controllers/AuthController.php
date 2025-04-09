<?php


namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        Log::info('Login attempt', ['email' => $request->email]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::info('User not found, creating...', ['email' => $request->email]);

            $user = User::create([
                'name' => explode('@', $request->email)[0],
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Log::info('User created', ['user_id' => $user->id]);
            return $this->createToken($user);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            Log::warning('Invalid login', ['email' => $request->email]);
            return response()->json([
                'message' => 'Неверные учетные данные',
            ], 401);
        }

        Log::info('Login successful', ['user_id' => $user->id]);
        return $this->createToken($request->user());
    }

    private function createToken($user)
    {
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout()
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Вы успешно вышли',
        ]);
    }

    public function me()
    {
        return response()->json(request()->user());
    }
}
