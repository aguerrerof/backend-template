<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Para capturar errores de validación en JSON
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'data'     => null,
            'message'  => null,
            'error'    => __('custom.error_trying_to_process_request'),
            'devError' => $exception->getMessage(),
            'errors'   => $exception->errors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
    }

    /**
     * Renderizado global para otros errores
     */
    public function render($request, Throwable $e): Response
    {
        if ($this->hasSessionExpired($e)) {
            return $this->handleSessionExpiration($request);
        }

        if ($this->shouldReturnJson($request, $e)) {
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'data'    => null,
                    'message' => null,
                    'error'   => 'Unauthenticated.',
                    'errors'  => []
                ], Response::HTTP_UNAUTHORIZED);
            }

            if ($this->isHttpException($e)) {
                $statusCode = $e->getStatusCode();
                $message = Response::$statusTexts[$statusCode] ?? 'An error occurred.';

                return response()->json([
                    'data'    => null,
                    'message' => null,
                    'error'   => $message,
                    'errors'  => []
                ], $statusCode);
            }

            $statusCode = $e->getCode() > 0 ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = config('app.debug') ? $e->getMessage() : 'An internal error occurred.';

            return response()->json([
                'data'    => null,
                'message' => null,
                'error'   => $message,
                'errors'  => []
            ], $statusCode);
        }

        return response()->view('errors.general', [
            'message' => 'Ocurrió un error inesperado. Por favor intenta nuevamente o regresa a la pantalla anterior.',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Decidir cuándo devolver JSON (solo API o peticiones con Accept: application/json).
     */
    public function shouldReturnJson($request, Throwable $e): bool
    {
        if ($request->expectsJson()) {
            return true;
        }

        if ($this->isHttpException($e)) {
            return true;
        }

        return $request->is('api/*');
    }

    private function hasSessionExpired(Throwable $e): bool
    {
        if ($e instanceof TokenMismatchException) {
            return true;
        }

        return $this->isHttpException($e) && $e->getStatusCode() === 419;
    }

    private function handleSessionExpiration($request): Response
    {
        $message = __('custom.session_expired', [], app()->getLocale());

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'data'    => null,
                'message' => null,
                'error'   => $message,
                'errors'  => [],
            ], 419);
        }

        return redirect()->route('login')->with('error', $message);
    }
}
