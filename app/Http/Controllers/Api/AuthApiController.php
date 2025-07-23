<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class AuthApiController extends BaseApiController
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Validation failed', 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('API Token')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];

        return $this->successResponse($data, 'User registered successfully', 201);
    }

    /**
     * Login user and create token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Validation failed', 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse(null, 'Invalid credentials', 401);
        }

        $user = Auth::user();
        $token = $user->createToken('API Token')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];

        return $this->successResponse($data, 'Login successful');
    }

    /**
     * Logout user (Revoke the token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout successful');
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'User retrieved successfully');
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        // Delete current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('API Token')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];

        return $this->successResponse($data, 'Token refreshed successfully');
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(null, 'Logged out from all devices successfully');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Validation failed', 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse(null, 'Current password is incorrect', 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->successResponse(null, 'Password changed successfully');
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Validation failed', 422);
        }

        $user = $request->user();
        $user->update($request->only('name', 'email'));

        return $this->successResponse($user, 'Profile updated successfully');
    }

    /**
     * Get user tokens
     */
    public function tokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->get(['id', 'name', 'last_used_at', 'created_at']);

        return $this->successResponse($tokens, 'Tokens retrieved successfully');
    }

    /**
     * Revoke specific token
     */
    public function revokeToken(Request $request, $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->where('id', $tokenId)->first();

        if (!$token) {
            return $this->errorResponse(null, 'Token not found', 404);
        }

        $token->delete();

        return $this->successResponse(null, 'Token revoked successfully');
    }
}
