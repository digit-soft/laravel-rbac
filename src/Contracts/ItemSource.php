<?php

namespace DigitSoft\LaravelRbac\Contracts;

/**
 * Interface PermissionSource
 * @package DigitSoft\LaravelRbac\Contracts
 */
interface ItemSource
{
    const TYPE_PERMISSION = 1;
    const TYPE_ROLE = 2;

    /**
     * Get all items
     * @param int $type
     * @return Permission[]|Role[]
     */
    public function getItems($type = null);

    /**
     * Get item
     * @param string $name
     * @return Permission|Role
     */
    public function getItem($name);

    /**
     * Save item
     * @param Permission|Role $item
     * @return bool
     */
    public function saveItem($item);

    /**
     * Remove item
     * @param Permission|Role $item
     * @param int             $type
     * @return bool
     */
    public function removeItem($item, $type = self::TYPE_PERMISSION);

    /**
     * Add child to item
     * @param Permission|Role $item
     * @param Permission|Role $child
     * @return $this
     */
    public function addChild($item, $child);

    /**
     * Remove item child
     * @param Permission|Role $item
     * @param Permission|Role $child
     * @return $this
     */
    public function removeChild($item, $child);

    /**
     * Remove all children
     * @param Permission|Role $item
     * @return $this
     */
    public function removeChildren($item);

    /**
     * Get item children
     * @param string $parentName
     * @param int    $parentType
     * @param bool   $recursive
     * @return mixed
     */
    public function getChildren($parentName, $parentType = self::TYPE_PERMISSION, $recursive = false);

    /**
     * Get user assignments
     * @param int      $user_id
     * @param int|null $type
     * @return Permission[]|Role[]
     */
    public function getAssignments($user_id, $type = null);

    /**
     * Add user assignment
     * @param int $user_id
     * @param Permission|Role $item
     * @return $this
     */
    public function addAssignment($user_id, $item);

    /**
     * Remove user assignment
     * @param int             $user_id
     * @param Permission|Role $item
     * @return $this
     */
    public function removeAssignment($user_id, $item);

    /**
     * Remove all user assignments
     * @param int $user_id
     * @return $this
     */
    public function removeAssignments($user_id);

    /**
     * Flush all data
     */
    public function flushAll();
}