<?php

namespace App\Http\Middleware;

use App\Http\Traits\apiResponse;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    use apiResponse;
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $this->apiResponse(401, 'unauthorized');
    }
}
