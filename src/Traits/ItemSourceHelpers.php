<?php

namespace DigitSoft\LaravelRbac\Traits;

use DigitSoft\LaravelRbac\Contracts\ItemSource;
use DigitSoft\LaravelRbac\Contracts\Permission;
use DigitSoft\LaravelRbac\Contracts\Role;
use Illuminate\Support\Str;

/**
 * Trait ItemSourceHelpers
 * @package DigitSoft\LaravelRbac\Traits
 */
trait ItemSourceHelpers
{
    protected $items;

    protected $itemsByType;

    protected $children;

    protected $parents;


    /**
     * Get items
     * @param int|null          $type
     * @param string|array|null $names
     * @return array
     */
    protected function getItemsInternal($type = null, $names = null)
    {
        if (!isset($this->items)) {
            $this->loadItems();
        }
        if ($type !== null) {
            $items = $this->itemsByType[$type] ?? [];
        } else {
            $items = $this->items;
        }
        if ($names !== null) {
            $names = is_array($names) ? $names : [$names];
            $items = array_filter($items, function ($value) use ($names) {
                /** @var Permission|Role $value */
                return in_array($value->name, $names);
            });
        }
        return $items;
    }

    /**
     * Get all children relations
     * @param string|null $parentName
     * @return array
     */
    protected function getChildrenInternal($parentName = null)
    {
        if (!isset($this->children)) {
            $this->loadItems();
        }
        if ($parentName !== null) {
            return $this->children[$parentName] ?? [];
        }
        return $this->children;
    }

    /**
     * Process items obtained from file
     * @param array $itemsRaw
     * @return array
     */
    protected function processRawArrayItems($itemsRaw)
    {
        $items = $itemsByType = $children = $parents = [];
        foreach ($itemsRaw as $name => $item) {
            $this->fillItemArray($item, $name);
            $type = $item['type'];
            $children[$name] = $item['children'];
            $parents[$name] = $parents[$name] ?? [];
            if (!empty($item['children'])) {
                foreach ($item['children'] as $childName) {
                    if ($childName === '*') {
                        continue;
                    }
                    $parents[$childName] = $parents[$childName] ?? [];
                    $parents[$childName][] = $name;
                }
            }
            $itemObject = $this->populateItemObject($item);
            $items[$name] = $itemObject;
            $itemsByType[$type][$name] = $itemObject;
        }

        foreach ($children as $name => $childrenInt) {
            $this->getChildrenRecursiveInternal($name, $children);
        }

        foreach ($parents as $name => $parentInt) {
            $this->getParentsRecursiveInternal($name, $parents);
        }

        ksort($items);
        ksort($itemsByType);

        return [$items, $children, $parents, $itemsByType];
    }

    /**
     * Get all children for item by name
     * @param string $name
     * @param array  $children
     * @return array
     */
    protected function getChildrenRecursiveInternal($name, &$children)
    {
        if ($name === '*') {
            return [];
        }

        foreach ($children[$name] as $childName) {
            $childrenInt = $this->getChildrenRecursiveInternal($childName, $children);
            if (!empty($childrenInt)) {
                $children[$name] = array_merge($children[$name], $childrenInt);
            }
        }
        sort($children[$name]);
        return $children[$name];
    }

    /**
     * Get all parents for item by name
     * @param string $name
     * @param array  $parents
     * @return array
     */
    protected function getParentsRecursiveInternal($name, &$parents)
    {
        if ($name === '*') {
            return [];
        }

        foreach ($parents[$name] as $parentName) {
            $parentsInt = $this->getChildrenRecursiveInternal($parentName, $parents);
            if (!empty($parentsInt)) {
                $parents[$name] = array_merge($parents[$name], $parentsInt);
            }
        }
        sort($parents[$name]);
        return $parents[$name];
    }

    /**
     * Load items from file
     */
    protected function loadItems()
    {
        $itemsRaw = $this->loadSourceItemArray();
        list($this->items, $this->children, $this->parents, $this->itemsByType) = $this->processRawArrayItems($itemsRaw);
    }

    /**
     * Fill item array with needed data
     * @param array  $item
     * @param string $name
     */
    protected function fillItemArray(&$item, $name)
    {
        $type = $item['type'] ?? ItemSource::TYPE_PERMISSION;
        $item = [
            'name' => $name,
            'title' => $item['title'] ?? Str::ucfirst(str_replace('.', ' ', $name)),
            'description' => $item['description'] ?? null,
            'type' => $type,
            'children' => $item['children'] ?? [],
        ];
    }

    /**
     * Populate item object with data
     * @param array $item
     * @return Permission|Role
     */
    protected function populateItemObject(array $item)
    {
        $className = isset($item['type']) && $item['type'] === static::TYPE_ROLE ? Role::class : Permission::class;
        return app()->make($className, ['config' => $item]);
    }

    /**
     * Flush data in memory
     */
    protected function flushInMemory()
    {
        $this->getAssignsRaw(true);
        $this->getItemsRaw(true);
    }

    /**
     * Flush cache data
     */
    protected function flushCache()
    {
        $keys = ['items', 'assignments'];
        if(method_exists(get_called_class(), 'forgetCache')) {
            $this->forgetCache($keys);
        }
    }

    /**
     * Load items from source as array with children
     * @return array
     */
    abstract protected function loadSourceItemArray();
}