<?php

namespace DigitSoft\LaravelRbac;

use DigitSoft\LaravelRbac\Contracts\AccessChecker;
use DigitSoft\LaravelRbac\Contracts\Item;
use DigitSoft\LaravelRbac\Contracts\Storage;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class RbacManager.
 * Created to simplify work with RBAC.
 * Use Rbac facade to methods access.
 * @package DigitSoft\LaravelRbac
 */
class RbacManager
{
    /**
     * @var Storage
     */
    protected $storage;
    /**
     * @var AccessChecker
     */
    protected $checker;

    /**
     * RbacManager constructor.
     * @param Storage       $storage
     * @param AccessChecker $checker
     */
    public function __construct(Storage $storage, AccessChecker $checker)
    {
        $this->storage = $storage;
        $this->checker = $checker;
    }

    /**
     * Check that user has at least one of permissions or roles
     * @param string|array $permissions
     * @param int|null     $user_id
     * @return bool
     */
    public function has($permissions, $user_id = null)
    {
        return $this->checker->has($permissions, $user_id);
    }

    /**
     * Check that user does not have any of those permissions
     * @param string|array $permissions
     * @param int|null     $user_id
     * @return bool
     */
    public function hasNo($permissions, $user_id = null)
    {
        return ! $this->has($permissions, $user_id);
    }

    /**
     * Get permission by name
     * @param string $name
     * @return Contracts\Permission|null
     */
    public function getPermission(string $name)
    {
        return $this->storage->getItem($name, Item::TYPE_PERMISSION);
    }

    /**
     * Get role by name
     * @param string $name
     * @return Contracts\Role|null
     */
    public function getRole(string $name)
    {
        return $this->storage->getItem($name, Item::TYPE_ROLE);
    }

    /**
     * Get item by name
     * @param string $name
     * @return Contracts\ItemSimple|null
     */
    public function get(string $name)
    {
        return $this->storage->getItem($name);
    }

    /**
     * Save item to storage
     * @param Contracts\Permission|Contracts\Role $item
     * @return bool
     */
    public function save($item)
    {
        return $this->storage->saveItem($item);
    }

    /**
     * Delete item from storage
     * @param Contracts\Permission|Contracts\Role|string $item
     */
    public function delete($item)
    {
        if (is_string($item)) {
            $item = $this->get($item);
        }
        if ($item instanceof Item) {
            $this->storage->removeItem($item);
        }
    }

    /**
     * Add child to item
     * @param Contracts\Permission|Contracts\Role $item
     * @param Contracts\Permission|Contracts\Role $child
     * @return bool
     */
    public function attach($item, $child)
    {
        return $this->storage->addItemChild($child, $item);
    }

    /**
     * Remove child from item
     * @param Contracts\Permission|Contracts\Role $item
     * @param Contracts\Permission|Contracts\Role $child
     */
    public function detach($item, $child)
    {
        $this->storage->removeItemChild($child, $item);
    }

    /**
     * Remove all children from item
     * @param Contracts\Permission|Contracts\Role $item
     */
    public function detachAll($item)
    {
        $this->storage->removeItemChildren($item);
    }

    /**
     * Add assignment to user
     * @param Contracts\Permission|Contracts\Role $item
     * @param int $user_id
     * @return bool
     */
    public function assign($item, int $user_id)
    {
        return $this->storage->addAssignment($item, $user_id);
    }

    /**
     * Remove assignment from user
     * @param Contracts\Permission|Contracts\Role $item
     * @param int                                 $user_id
     */
    public function revoke($item, int $user_id)
    {
        $this->storage->removeAssignment($item, $user_id);
    }

    /**
     * Remove all user assignments
     * @param int $user_id
     */
    public function revokeAll(int $user_id)
    {
        $this->storage->removeAssignments($user_id);
    }

    /**
     * Setter for storage
     * @param Storage $storage
     */
    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Get all user assigned roles
     * @param  int $user_id
     * @return Models\Role[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getUserRoles(int $user_id)
    {
        return $this->getUserPermissionsInternal($user_id, Item::TYPE_ROLE);
    }

    /**
     * Get all user assigned permissions
     * @param  int $user_id
     * @return Models\Permission[]|Models\Role[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getUserPermissions(int $user_id)
    {
        return $this->getUserPermissionsInternal($user_id, Item::TYPE_PERMISSION);
    }

    /**
     * Get all user assigned items
     * @param  int     $user_id
     * @param  int|null $type
     * @return Models\Permission[]|Models\Role[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function getUserPermissionsInternal($user_id, $type = null)
    {
        $query = Models\Permission::query()
            ->withoutGlobalScope('type')
            ->whereHas('assignments', function ($queryInt) use ($user_id) {
                /** @var $queryInt Builder */
                $queryInt->where('user_id', '=', $user_id);
            });
        $query->with('children.children.children')->orderBy('name');
        /** @var Models\Permission[]|Models\Role[]|\Illuminate\Database\Eloquent\Collection $models */
        $models = $query->get();
        if (empty($models)) {
            return collect([]);
        }
        $models = Models\Permission::unpackItems($models, 'children');
        if ($type === null) {
            return collect($models);
        }
        $results = [];
        foreach ($models as $model) {
            if ($model->type === $type) {
                $results[] = $model;
            }
        }
        return collect($results);
    }
}