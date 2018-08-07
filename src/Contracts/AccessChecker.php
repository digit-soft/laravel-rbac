<?php

namespace DigitSoft\LaravelRbac\Contracts;

use Illuminate\Http\Request;

interface AccessChecker
{
    /**
     * Check that user has permission or role by name
     * @param array|string $names
     * @param int|null     $user_id
     * @return bool
     */
    public function has($names, $user_id = null);

    /**
     * Set current request
     * @param Request $request
     */
    public function setRequest(Request $request);
}