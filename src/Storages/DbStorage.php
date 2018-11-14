<?php

namespace DigitSoft\LaravelRbac\Storages;

use App\Models\Collection;
use DigitSoft\LaravelRbac\Contracts\Item;
use DigitSoft\LaravelRbac\Contracts\Storage;
use DigitSoft\LaravelRbac\Models\Assignment;
use DigitSoft\LaravelRbac\Models\Permission;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class DbStorage implements Storage
{
    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * DbStorage constructor.
     * @param DatabaseManager $db
     */
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    /**
     * Get all items
     * @param int|null $type
     * @return Item[]
     */
    public function getItems($type = null)
    {
        return $this->getItemsInternal(null, $type);
    }

    /**
     * Get item by name
     * @param string   $name
     * @param int|null $type
     * @return Item|null
     */
    public function getItem($name, $type = null)
    {
        $query = Permission::query()->withoutGlobalScope('type')
            ->where('name', '=', $name);
        if (null !== $type) {
            $query->where('type', '=', $type);
        }
        return $query->first();
    }

    /**
     * Save item to storage
     * @param Item $item
     * @return bool
     */
    public function saveItem($item)
    {
        return $item->save();
    }

    /**
     * Remove item from storage
     * @param Item $item
     * @return void
     */
    public function removeItem($item)
    {
        if ($item->id === null) {
            return;
        }
        $item->delete();
    }

    /**
     * Add item child
     * @param  Item $child
     * @param  Item $item
     * @return bool
     */
    public function addItemChild($child, $item)
    {
        if (!$item->canBeAttached($child)) {
            return false;
        }
        $item->children()->attach($child);
        return true;
    }

    /**
     * Remove child from item or from all items
     * @param  Item|null $child
     * @param  Item|null $item
     * @return void
     */
    public function removeItemChild($child, $item = null)
    {
        if ($item === null) {
            $child->parents()->detach();
        } else {
            $item->children()->detach($child);
        }
    }

    /**
     * Get item children
     * @param  Item|null $item
     * @param  bool      $onlyNames
     * @return Item[]|string[]
     */
    public function getItemChildren($item = null, $onlyNames = false)
    {
        if ($item !== null) {
            $children = $item->children()->get();
            return $onlyNames ? Arr::pluck($children, 'name') : $children;
        }
        /** @var Collection|Permission[] $items */
        $items = Permission::withoutGlobalScope('type')
            ->with('children')
            ->orderBy('name')
            ->get();
        $children = [];
        foreach ($items as $row) {
            $children[$row->name] = $onlyNames
                ? $row->children->pluck('name')->all()
                : $row->children->pluck(null, 'name');
        }
        return $children;
    }

    /**
     * Remove children from item
     * @param  Item $item
     * @return void
     */
    public function removeItemChildren($item)
    {
        $item->children()->delete();
    }

    /**
     * Get user assignments.
     * From one user as indexed array or from all users as array keyed by user id.
     * @param int $user_id
     * @param bool     $onlyNames
     * @param int|null $type
     * @return array
     */
    public function getAssignments($user_id, $onlyNames = false, $type = null)
    {
        $query = Permission::query()->withoutGlobalScope('type');
        $query->whereHas('assignments', function ($queryInt) use ($user_id) {
            /** @var Builder $queryInt */
            $queryInt->where('user_id', '=', $user_id);
        });
        if ($type !== null) {
            $query->where('type', '=', $type);
        }
        $items = $query->get();

        return $onlyNames ? $items->pluck('name')->toArray() : $items;
    }

    /**
     * Add user assignment
     * @param  Item     $item
     * @param  int|null $user_id
     * @return bool
     */
    public function addAssignment($item, $user_id)
    {
        $exist = $item->assignments()->where('user_id', '=', $user_id)->exists();
        if ($exist) {
            return false;
        }
        $model = $item->assignments()->create(['user_id' => $user_id]);
        return $model instanceof Assignment;
    }

    /**
     * Remove assignment (from one user or all)
     * @param  Item     $item
     * @param  int|null $user_id
     * @return void
     */
    public function removeAssignment($item, $user_id = null)
    {
        $query = $item->assignments();
        if ($user_id !== null) {
            $query->where('user_id', '=', $user_id);
        }
        $query->delete();
    }

    /**
     * Remove all user assignments
     * @param  int $user_id
     * @return void
     */
    public function removeAssignments($user_id)
    {
        Assignment::query()
            ->where('user_id', '=', $user_id)
            ->delete();
    }

    /**
     * Get items internal helper
     * @param  array|null $names
     * @param  string|null $type
     * @return array
     */
    protected function getItemsInternal($names = null, $type = null)
    {
        $query = Permission::query()->withoutGlobalScope('type');
        if ($names !== null) {
            $names = is_array($names) ? $names : [$names];
            $query->whereIn('name', $names);
        }
        if ($type !== null) {
            $query->where('type', '=', $type);
        }
        $items = $query->orderBy('name')
            ->get()->pluck(null, 'name')->toArray();
        return $items;
    }
}