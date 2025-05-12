<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\{AccessDeniedHttpException,
    BadRequestHttpException,
    MethodNotAllowedHttpException,
    NotFoundHttpException,
    UnauthorizedHttpException,
    UnsupportedMediaTypeHttpException,};
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The names of the attributes that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        Log::error('Exception occurred', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'exception' => $e
        ]);

        // Not Found Exception (Model or Route)
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return $this->formatErrorResponse('The requested model was not found.', 404);
        }


        // Method Not Allowed
        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->formatErrorResponse('The HTTP method is not allowed for this route.', 405);
        }
        // Access Denied
        if ($e instanceof UnauthorizedHttpException || $e instanceof AccessDeniedHttpException) {
            return $this->formatErrorResponse('You are not allowed to perform this action.', 403);
        }

        if ($e instanceof AuthorizationException) {
            return $this->formatErrorResponse('You do not have permission to access this resource.', 403);
        }

        if ($e instanceof AuthenticationException) {
            return $this->formatErrorResponse('Unauthenticated, please login.', 401);
        }

        // Too Many Requests
        if ($e instanceof ThrottleRequestsException) {
            return $this->formatErrorResponse('Too many requests. Please slow down.', 429);
        }

        // Bad Request
        if ($e instanceof BadRequestHttpException) {
            return $this->formatErrorResponse('Bad request. Please check your input.', 400);
        }

        // Unsupported Media Type
        if ($e instanceof UnsupportedMediaTypeHttpException) {
            return $this->formatErrorResponse('Unsupported media type.', 415);
        }

        // Query Exception
        if ($e instanceof QueryException) {
            return $this->formatErrorResponse('A database query error occurred.', 500);
        }

        // Exception
        if ($e instanceof \Exception) {
            return $this->formatErrorResponse($e->getMessage(), 500);
        }

        // General Unexpected Errors
        if (config('app.debug')) {
            return parent::render($request, $e); // Default Laravel error page for debugging
        }

        return $this->formatErrorResponse('An unexpected error occurred.', 500);
    }

    /**
     * Format the error response for JSON output.
     */
    protected function formatErrorResponse(string $message, int $statusCode, $errors = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
