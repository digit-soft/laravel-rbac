<?php

namespace DigitSoft\LaravelRbac\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Rbac.
 * Facade for RBAC manager
 * @package DigitSoft\LaravelRbac\Facades
 * @see \DigitSoft\LaravelRbac\RbacManager
 */
class Rbac extends Facade
{
    /**
     * @inheritdoc
     */
    protected static function getFacadeAccessor()
    {
        return 'rbac';
    }
}