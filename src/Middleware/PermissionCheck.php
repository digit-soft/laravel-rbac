<?php

namespace DigitSoft\LaravelRbac\Middleware;

use DigitSoft\LaravelRbac\Facades\Rbac;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class PermissionCheck.
 * Middleware for permissions|roles check
 * @package DigitSoft\LaravelRbac\Middleware
 */
class PermissionCheck
{
    /**
     * Handle request
     * Check permissions or roles. Permissions can be passed as `|` separated array
     * @param  Request      $request
     * @param  \Closure     $next
     * @param  string|array $permission
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, $permission)
    {
        if (auth()->guest()) {
            throw new AccessDeniedHttpException();
        }
        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);
        if (!Rbac::has($permissions)) {
            throw new AccessDeniedHttpException();
        }
        return $next($request);
    }
}