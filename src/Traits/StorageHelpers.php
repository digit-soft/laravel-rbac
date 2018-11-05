<?php

namespace DigitSoft\LaravelRbac\Traits;

use DigitSoft\LaravelRbac\Contracts\ItemSimple;
use DigitSoft\LaravelRbac\Contracts\Permission;
use DigitSoft\LaravelRbac\Contracts\Role;
use Illuminate\Support\Str;

trait StorageHelpers
{
    /**
     * Get items according to properties values
     * @param array $conditions
     * @param array $sourceData
     * @param bool  $objects
     * @return array
     */
    protected function findDataConditionally($conditions = [], $sourceData = [], $objects = false)
    {
        if (empty($conditions)) {
            return $this->items;
        }
        if ($objects) {
            $filterFunction = function ($item) use ($conditions) {
                /** @var Permission|Role $item */
                foreach ($conditions as $property => $value) {
                    if (
                        $value === null
                        || $item->{$property} === $value
                        || (is_array($value) && in_array($item->{$property}, $value))
                    ) {
                        continue;
                    }
                    return false;
                }
                return true;
            };
        } else {
            $filterFunction = function ($item) use ($conditions) {
                /** @var Permission|Role $item */
                foreach ($conditions as $property => $value) {
                    if (
                        $value === null
                        || $item[$property] === $value
                        || (is_array($value) && in_array($item[$property], $value))
                    ) {
                        continue;
                    }
                    return false;
                }
                return true;
            };
        }
        return array_filter($sourceData, $filterFunction, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Normalize item array
     * @param array  $itemArray
     * @param string $name
     * @param bool   $removeChildren
     */
    protected function normalizeItemArray(array &$itemArray, $name, $removeChildren = false)
    {
        $id = $itemArray['id'] ?? null;
        $itemArray = [
            'name' => $name,
            'title' => $itemArray['title'] ?? Str::ucfirst(str_replace('.', ' ', $name)),
            'description' => $itemArray['description'] ?? null,
            'type' => $itemArray['type'] ?? ItemSimple::TYPE_PERMISSION,
            'children' => $itemArray['children'] ?? [],
        ];
        if ($id !== null) {
            $itemArray['id'] = $id;
        }
        if ($removeChildren) {
            unset($itemArray['children']);
        }
    }

    /**
     * Create Item from data array
     * @param array|object $data
     * @return ItemSimple
     */
    protected function populateItem($data = [])
    {
        $data = (array)$data;
        $itemClass = isset($data['type']) && $data['type'] === ItemSimple::TYPE_ROLE ? Role::class : Permission::class;
        return app()->make($itemClass, ['config' => $data]);
    }
}