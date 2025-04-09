<?php


namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            Log::info('Login attempt', ['email' => $request->email]);

            // Check if user exists
            $user = User::where('email', $request->email)->first();

            // If user doesn't exist, create a new one (auto-registration)
            if (!$user) {
                Log::info('User does not exist, creating new user', ['email' => $request->email]);

                $user = User::create([
                    'name' => explode('@', $request->email)[0],
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                Log::info('User created successfully', ['user_id' => $user->id]);

                return $this->createToken($user);
            }

            // Verify credentials
            if (!Auth::attempt($request->only('email', 'password'))) {
                Log::warning('Invalid credentials', ['email' => $request->email]);

                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            Log::info('User logged in successfully', ['user_id' => $user->id]);

            return $this->createToken($request->user());
        } catch (ValidationException $e) {
            Log::error('Validation error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Login error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'An error occurred during login.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function createToken($user)
    {
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'An error occurred during logout.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            return response()->json($request->user());
        } catch (\Exception $e) {
            Log::error('Get user error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'An error occurred while getting user data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
