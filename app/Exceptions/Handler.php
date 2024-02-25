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
            return $this->apiResponse(401, 'error 401', 'You Not Authorized to access this route, try with correct route');
        }
        if ($e instanceof NotFoundHttpException) {
            return $this->apiResponse(404, 'error 404', $request->url().' Not Found, try with correct url');
        }
        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->apiResponse(405, 'error 405', $request->method().' method Not allow for this route, try with correct method');
        }
        if ($e instanceof ValidationException) {
            return $this->apiResponse(422, 'Unprocessable Content', $e->errors());
        }

    }
}
