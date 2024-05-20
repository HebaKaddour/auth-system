<?php

namespace App\Exceptions;
use Throwable;
use Illuminate\Http\JsonResponse;
use App\Http\Traits\ApiResposeTrait;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    use ApiResposeTrait;
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {
            return $this->errorResponse('Unauthenticated', 401);
        }
        if ($exception instanceof ModelNotFoundException) {
            return $this->errorResponse('Resource not found', 404);
        }
        if ($exception instanceof ValidationException) {
            $errors = $exception->validator->errors()->toArray();
            return $this->errorResponse($errors, 422);
        }
        if ($exception instanceof UniqueConstraintViolationException) {
            $message = 'The the profile photo or certificate already exists. Please select others';
            $errors = $exception->getMessage();
            return $this->errorResponse($message, 422);

        }
        if ($exception instanceof HttpException) {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }
        if ($exception instanceof \ErrorException && strpos($exception->getMessage(), 'Undefined variable') !== false) {
            $message = 'Undefined variable';
            return $this->errorResponse( $message , 500);

        }

            if ($exception instanceof \BadMethodCallException && strpos($exception->getMessage(), 'does not exist') !== false) {
                $message = 'Method does not exist';
                return $this->errorResponse($message , 500);

            }
        return parent::render($request, $exception);
    }
}
