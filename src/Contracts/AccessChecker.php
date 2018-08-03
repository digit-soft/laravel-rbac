<?php

namespace DigitSoft\LaravelRbac\Contracts;

use Illuminate\Http\Request;

interface AccessChecker
{
    /**
     * Check that user has permission or role by name
     * @param string   $name
     * @param int|null $user_id
     * @return bool
     */
    public function has($name, $user_id = null);

    /**
     * Set current request
     * @param Request $request
     */
    public function setRequest(Request $request);
}