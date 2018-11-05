<?php

namespace DigitSoft\LaravelRbac\Traits;

use DigitSoft\LaravelRbac\Models\Assignment;
use DigitSoft\LaravelRbac\Models\Permission;
use DigitSoft\LaravelRbac\Models\Role;

/**
 * Trait HasRbac for User model.
 * @package DigitSoft\LaravelRbac\Traits
 * @property-read Role[]       $roles
 * @property-read Permission[] $permissions
 * @property-read Assignment[] $assignments
 */
trait HasRbac
{
    /**
     * Get all user assigned roles
     * @return Role[]
     */
    public function getRolesAttribute()
    {
        return $this->rbacManager()->getUserRoles($this->getKey());
    }

    /**
     * Get all user assigned permissions
     * @return Permission[]
     */
    public function getPermissionsAttribute()
    {
        return $this->rbacManager()->getUserPermissions($this->getKey());
    }

    /**
     * Get assignments
     * Uses database
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'user_id');
    }

    /**
     * Get RBAC manager
     * @return \DigitSoft\LaravelRbac\RbacManager
     */
    private function rbacManager()
    {
        return app('rbac');
    }
}