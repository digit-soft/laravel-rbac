<?php

namespace DigitSoft\LaravelRbac;

use DigitSoft\LaravelRbac\Contracts\Role as RoleContract;

class Role extends Permission implements RoleContract
{
    /**
     * Get item type
     * @return int
     */
    public function type()
    {
        return static::TYPE_ROLE;
    }
}