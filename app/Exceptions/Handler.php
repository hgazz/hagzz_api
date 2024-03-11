<?php

namespace App\Exceptions;

use App\Http\Traits\apiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use apiResponse;
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     * @throws Throwable
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof AuthenticationException) {
            return $this->apiResponse(401, 'error 401', 'You are not authorized to access this route. Please try with the correct route.');
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->apiResponse(404, 'error 404', $request->url().' not found. Please try with the correct URL.');
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->apiResponse(405, 'error 405', $request->method().' method is not allowed for this route. Please try with the correct method.');
        }

        if ($e instanceof ValidationException) {
            return $this->apiResponse(422, 'Unprocessable Content', $e->errors());
        }

        return parent::render($request, $e);

    }
}
