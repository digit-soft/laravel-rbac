<?php

namespace DigitSoft\LaravelRbac\Contracts;

/**
 * Interface Storage
 * @package DigitSoft\LaravelRbac\Contracts
 */
interface Storage
{
    /**
     * Get all items
     * @param int|null $type
     * @return Item[]
     */
    public function getItems($type = null);

    /**
     * Get item by name
     * @param string   $name
     * @param int|null $type
     * @return Item|null
     */
    public function getItem($name, $type = null);

    /**
     * Save item to storage
     * @param  Item $item
     * @return bool
     */
    public function saveItem($item);

    /**
     * Remove item from storage
     * @param  Item $item
     * @return void
     */
    public function removeItem($item);

    /**
     * Add item child
     * @param  Item $child
     * @param  Item $item
     * @return bool
     */
    public function addItemChild($child, $item);

    /**
     * Remove child from item or from all items
     * @param  Item|null $child
     * @param  Item|null $item
     * @return void
     */
    public function removeItemChild($child, $item = null);

    /**
     * Get item children
     * @param  Item|null $item
     * @param  bool      $onlyNames
     * @return Item[]|string[]
     */
    public function getItemChildren($item = null, $onlyNames = false);

    /**
     * Remove children from item
     * @param  Item $item
     * @return void
     */
    public function removeItemChildren($item);

    /**
     * Get user assignments.
     * From one user as indexed array or from all users as array keyed by user id.
     * @param int|null $user_id
     * @param bool     $onlyNames
     * @param int|null $type
     * @return array
     */
    public function getAssignments($user_id = null, $onlyNames = false, $type = null);

    /**
     * Add user assignment
     * @param Item     $item
     * @param int|null $user_id
     * @return bool
     */
    public function addAssignment($item, $user_id);

    /**
     * Remove assignment (from one user or all)
     * @param Item     $item
     * @param int|null $user_id
     * @return void
     */
    public function removeAssignment($item, $user_id = null);

    /**
     * Remove all user assignments
     * @param int $user_id
     * @return void
     */
    public function removeAssignments($user_id);
}