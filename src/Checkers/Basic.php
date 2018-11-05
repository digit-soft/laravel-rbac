<?php

namespace DigitSoft\LaravelRbac\Checkers;

use DigitSoft\LaravelRbac\Contracts\AccessChecker;
use DigitSoft\LaravelRbac\Contracts\ItemSimple;
use DigitSoft\LaravelRbac\Contracts\Storage;
use DigitSoft\LaravelRbac\Traits\WorksWithExpandedItems;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Basic implements AccessChecker
{
    use WorksWithExpandedItems;

    /**
     * @var Storage
     */
    protected $storage;
    /**
     * @var Request
     */
    protected $request;
    /**
     * Checks cache by user ID and item name
     * @var array
     */
    protected $checks = [];

    /**
     * @var ItemSimple[]|null
     */
    protected $items;
    /**
     * @var ItemSimple[][]|null
     */
    protected $itemsByType;
    /**
     * @var string[]|null
     */
    protected $children;
    /**
     * @var string[][]
     */
    protected $assignments = [];

    /**
     * Basic constructor.
     * @param Request $request
     * @param Storage $storage
     */
    public function __construct(Request $request, Storage $storage)
    {
        $this->request = $request;
        $this->storage = $storage;
    }

    /**
     * Check that user has permission or role by name
     * @param array|string $names
     * @param int|null     $user_id
     * @return bool
     */
    public function has($names, $user_id = null)
    {
        if ($user_id === null && ($user = $this->request->user()) !== null) {
            /** @var $user \Illuminate\Foundation\Auth\User */
            $user_id = $user->getAuthIdentifier();
        }
        if ($user_id === null) {
            return false;
        }
        $names = is_array($names) ? $names : [$names];
        foreach ($names as $name) {
            if (($checkResult = $this->getCheckCache($user_id, $name)) === null) {
                $checkResult = $this->checkAssignments($name, $this->getAssignments($user_id));
            }
            $this->setCheckCache($user_id, $name, $checkResult);
            if ($checkResult) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set current request
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $isNewRequest = $this->request === $request;
        $this->request = $request;
        if ($isNewRequest) {
            $this->reset();
        }
    }


    /**
     * Check user assignments
     * @param string $name
     * @param array  $assignments
     * @return bool
     */
    protected function checkAssignments($name, $assignments)
    {
        if (in_array($name, $assignments)) {
            return true;
        } elseif (!empty($assignments)) {
            $parents = $this->getParentsExpanded($name);
            foreach ($parents as $parentName) {
                if (in_array($parentName, $assignments)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get all user assignments
     * @param int $user_id
     * @return string[]
     */
    protected function getAssignments($user_id)
    {
        if (!isset($this->assignments[$user_id])) {
            $this->assignments[$user_id] = $this->storage->getAssignments($user_id, true);
        }
        return $this->assignments[$user_id];
    }

    /**
     * Get all items (or restrict by type)
     * @param int|null $type
     * @return \DigitSoft\LaravelRbac\Contracts\ItemSimple[]
     */
    protected function getItems($type = null)
    {
        if ($this->items === null) {
            $this->items = $this->storage->getItems();
            foreach ($this->items as $item) {
                $itemType = $item->type();
                $this->itemsByType[$itemType] = $this->itemsByType[$itemType] ?? [];
                $this->itemsByType[$itemType][$item->name] = $item;
            }
        }
        if ($type === null) {
            return $this->items;
        }
        return $this->itemsByType[$type] ?? [];
    }

    /**
     * Get all items children
     * @internal
     * @return string[]
     */
    protected function getChildren()
    {
        if ($this->children === null) {
            $this->children = $this->storage->getItemChildren(null, true);
        }
        return $this->children;
    }

    /**
     * Get check result from cache
     * @param int    $user_id
     * @param string $item_name
     * @return bool|null
     */
    protected function getCheckCache($user_id, $item_name)
    {
        $key = $user_id . '.' . $item_name;
        return Arr::get($this->checks, $key, null);
    }

    /**
     * Set check cache result
     * @param int    $user_id
     * @param string $item_name
     * @param bool   $result
     */
    protected function setCheckCache($user_id, $item_name, $result)
    {
        $key = $user_id . '.' . $item_name;
        Arr::set($this->checks, $key, boolval($result));
    }

    /**
     * Flush items cache
     */
    protected function flushItems()
    {
        $this->items = null;
        $this->children = null;
        $this->childrenExpanded = null;
        $this->parentsExpanded = null;
    }

    /**
     * Reset component state
     */
    protected function reset()
    {
        $this->checks = [];
        $this->assignments = [];
    }
}