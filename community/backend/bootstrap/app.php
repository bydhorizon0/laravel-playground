<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->throttleApi(); // api 라우트 그룹에 'throttle:api' 추가
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API 경로이거나 JSON 응답을 기대하는 요청에 대한 전역 예외 처리
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // 1. 유효성 검사 실패 (ValidationException -> 422)
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'success' => false,
                        'message' => '입력 데이터 유효성 검증에 실패했습니다.',
                        'errors' => $e->errors(),
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                // 2. 인증 실패 (AuthenticationException -> 401)
                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'message' => '인증되지 않은 요청입니다. 유효한 토큰이 필요합니다.',
                    ], Response::HTTP_UNAUTHORIZED);
                }

                // 3. 인가 실패 / 권한 부족 (AuthorizationException, AccessDeniedHttpException -> 403)
                if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                    $authException = $e instanceof AuthorizationException ? $e : $e->getPrevious();
                    $message = $authException instanceof AuthorizationException && $authException->getMessage()
                        ? $authException->getMessage()
                        : ($e->getMessage() ?: '해당 작업에 대한 접근 권한이 없습니다.');

                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], Response::HTTP_FORBIDDEN);
                }

                // 4. 리소스 미존재 (ModelNotFoundException, NotFoundHttpException -> 404)
                // Route Model Binding 실패 시 NotFoundHttpException의 previous에 ModelNotFoundException이 포함됩니다.
                $previousException = $e->getPrevious();
                if ($e instanceof ModelNotFoundException || $previousException instanceof ModelNotFoundException) {
                    /** @var ModelNotFoundException $modelException */
                    $modelException = $e instanceof ModelNotFoundException ? $e : $previousException;
                    $modelName = class_basename($modelException->getModel());

                    return response()->json([
                        'success' => false,
                        'message' => "요청하신 {$modelName} 리소스를 찾을 수 없습니다.",
                    ], Response::HTTP_NOT_FOUND);
                }

                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => '요청하신 API 엔드포인트 URL을 찾을 수 없습니다.',
                    ], Response::HTTP_NOT_FOUND);
                }

                // 5. 허용되지 않은 HTTP 메서드 (MethodNotAllowedHttpException -> 405)
                if ($e instanceof MethodNotAllowedHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => '지원하지 않는 HTTP 요청 메서드입니다.',
                    ], Response::HTTP_METHOD_NOT_ALLOWED);
                }

                // 6. API 요청 제한 초과 (ThrottleRequestsException -> 429)
                if ($e instanceof ThrottleRequestsException) {
                    return response()->json([
                        'success' => false,
                        'message' => '단시간 내 요청 한도를 초과했습니다. 잠시 후 다시 시도해주세요.',
                    ], Response::HTTP_TOO_MANY_REQUESTS);
                }

                // 7. 기타 명시적 HTTP 예외 (HttpException)
                if ($e instanceof HttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage() ?: '요청 처리 중 오류가 발생했습니다.',
                    ], $e->getStatusCode());
                }

                // 8. 서버 내부 오류 (500 Internal Server Error)
                $isDebug = config('app.debug', false);

                return response()->json([
                    'success' => false,
                    'message' => $isDebug ? $e->getMessage() : '서버 내부 오류가 발생했습니다. 관리자에게 문의하세요.',
                    'errors' => $isDebug ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        // getTrace()에는 함수 인자(args: 비밀번호 등)가 포함될 수 있으므로 필요한 키만 추립니다.
                        'trace' => collect($e->getTrace())
                            ->take(5)
                            ->map(fn (array $frame): array => Arr::only($frame, ['file', 'line', 'class', 'function']))
                            ->all(),
                    ] : null,
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return null; // 웹(Web) 요청은 기본 라라벨 에러 페이지로 위임
        });
    })->create();
