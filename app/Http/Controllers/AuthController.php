<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthFormRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;


class AuthController extends Controller
{
  protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function user_Register(AuthFormRequest $request): JsonResponse
    {
        $result = $this->authService->user_Register($request->validated());

        return response()->json([
            'message' => 'User Created Successfully',
            'token' => $result['token'] ?? null
        ]);
    }

    public function resend_email(string $email): JsonResponse
    {
        $this->authService->resend_email($email);

        return response()->json([
            'message' => 'We have sent the code to your email address'
        ]);
    }

    public function verification(AuthFormRequest $request, string $email): JsonResponse
    {
        $success = $this->authService->verification($request->validated(), $email);

        return response()->json([
            'message' => $success ? 'your email has been verified successfully' : 'the code is wrong, please try again'
        ]);
    }

    public function reset_password(AuthFormRequest $request, string $email): JsonResponse
    {
        $this->authService->reset_password($request->validated(), $email);

        return response()->json([
            'message' => 'your password has changed successfully'
        ]);
    }

    public function create_owner(AuthFormRequest $request, string $email): JsonResponse
    {
        $result = $this->authService->create_owner($request->validated(), $email);

        return response()->json([
            'message' => $result,
        ]);
    }

    public function login(AuthFormRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if ($result === 'banned') {
            return response()->json(['message' => 'You are banned from using this web application.']);
        }
        if ($result === 'pending') {
            return response()->json(['message' => 'Your request is still being processed.']);
        }
        if (!$result) {
            return response()->json(['message' => 'Your email does not match with password.. Please try again.']);
        }

        return response()->json([
            'message' => 'Welcome',
            'token' => $result['token']
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'message' => 'You logged out successfully'
        ]);
    }
  
}
