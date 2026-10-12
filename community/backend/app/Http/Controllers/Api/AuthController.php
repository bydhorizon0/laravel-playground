<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

use function method_exists;
use function now;

#[Middleware('throttle:auth', only: ['register', 'login'])]
#[Middleware('auth:sanctum', only: ['logout', 'me'])]
class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create($validated);

        $token = $user->createToken('auth-token', ['*'], now()->addDays(30))->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], '회원가입이 완료되었습니다.1');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse(
                message: '이메일 또는 비밀번호가 올바르지 않습니다.',
                status: Response::HTTP_UNAUTHORIZED
            );
        }

        $deviceName = $validated['device_name'] ?? 'web';

        $token = $user->createToken($deviceName, ['*'], now()->addDays(30))->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], '로그인에 성공했습니다.');
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $token = $user->currentAccessToken();
        if ($token !== null && method_exists($token, 'delete')) {
            $token->delete();
        }

        return $this->successResponse(null, '성공적으로 로그아웃되었습니다.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            data: new UserResource($request->user()),
            message: '사용자 프로필 조회에 성공했습니다.'
        );
    }
}
