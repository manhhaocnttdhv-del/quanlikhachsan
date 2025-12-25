<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        $curentLogin = $request->route()->middleware();
        if (!empty($curentLogin) && in_array('auth:admin', $curentLogin)) {
            return route('admin.login');
        }
        return route('login');
    }

}