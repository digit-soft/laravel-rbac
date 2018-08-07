<?php

namespace DigitSoft\LaravelRbac\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Rbac.
 * Facade for RBAC manager
 * @package DigitSoft\LaravelRbac\Facades
 * @method static bool has(string $permissions, $user_id = null)
 * @method static bool hasNo(string $permissions, $user_id = null)
 * @method static \DigitSoft\LaravelRbac\Contracts\Permission|null getPermission(string $name)
 * @method static \DigitSoft\LaravelRbac\Contracts\Role|null getRole(string $name)
 * @method static \DigitSoft\LaravelRbac\Contracts\Role|\DigitSoft\LaravelRbac\Contracts\Permission|null get(string $name)
 * @method static bool save(\DigitSoft\LaravelRbac\Contracts\Item $item)
 * @method static void delete(\DigitSoft\LaravelRbac\Contracts\Item $item)
 * @method static bool attach(\DigitSoft\LaravelRbac\Contracts\Item $item, \DigitSoft\LaravelRbac\Contracts\Item $child)
 * @method static void detach(\DigitSoft\LaravelRbac\Contracts\Item $item, \DigitSoft\LaravelRbac\Contracts\Item $child)
 * @method static void detachAll(\DigitSoft\LaravelRbac\Contracts\Item $item)
 * @method static bool assign(\DigitSoft\LaravelRbac\Contracts\Item $item, int $user_id)
 * @method static void revoke(\DigitSoft\LaravelRbac\Contracts\Item $item, int $user_id)
 * @method static void revokeAll(int $user_id)
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