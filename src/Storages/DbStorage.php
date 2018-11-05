<?php

namespace DigitSoft\LaravelRbac\Storages;

use DigitSoft\LaravelRbac\Contracts\Item;
use DigitSoft\LaravelRbac\Contracts\Storage;
use DigitSoft\LaravelRbac\Traits\StorageHelpers;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;

class DbStorage implements Storage
{
    use StorageHelpers;

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
        $query = $this->itemsQuery()
            ->where('name', '=', $name);
        if (null !== $type) {
            $query->where('type', '=', $type);
        }
        $result = $query->first();
        return $result !== null ? $this->populateItem($result) : null;
    }

    /**
     * Save item to storage
     * @param Item $item
     * @return bool
     */
    public function saveItem($item)
    {
        $itemArray = $item->toArray();
        if ($item->id === null) {
            Arr::forget($itemArray, 'id');
            $id = $this->itemsQuery()->insertGetId($itemArray);
            $item->id = $id;
            return true;
        }
        unset($itemArray['id']);
        return $this->itemsQuery()->update($itemArray) > 0;
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
        $this->itemsQuery()->delete($item->id);
    }

    /**
     * Add item child
     * @param  Item $child
     * @param  Item $item
     * @return bool
     */
    public function addItemChild($child, $item)
    {
        $exists = $this->childrenQuery()
            ->where('parent_id', '=', $item->id)
            ->where('child_id', '=', $child->id)
            ->exists();
        if ($exists) {
            return false;
        }
        $record = [
            'parent_id' => $item->id,
            'child_id' => $child->id,
        ];
        return $this->childrenQuery()
            ->insert($record);
    }

    /**
     * Remove child from item or from all items
     * @param  Item|null $child
     * @param  Item|null $item
     * @return void
     */
    public function removeItemChild($child, $item = null)
    {
        $query = $this->childrenQuery();
        if ($item !== null) {
            $query->where('parent_id', '=', $item->id);
        }
        $query->where('child_id', '=', $child->id);
        $query->delete();
    }

    /**
     * Get item children
     * @param  Item|null $item
     * @param  bool      $onlyNames
     * @return Item[]|string[]
     */
    public function getItemChildren($item = null, $onlyNames = false)
    {
        $query = $this->childrenQuery('ch');
        $query->leftJoin(Item::DB_TABLE_ITEMS . ' AS it', 'ch.child_id', '=', 'it.id');
        $query->leftJoin(Item::DB_TABLE_ITEMS . ' AS itp', 'ch.parent_id', '=', 'itp.id');
        if ($item !== null) {
            $query->where('parent_id', '=', $item->id);
        }
        $results = $query->get(['it.*', 'itp.name as parent_name'])
            ->groupBy('parent_name')
            ->toArray();
        $items = [];
        if ($onlyNames) {
            foreach ($results as $parent_name => $rows) {
                $items[$parent_name] = Arr::pluck($rows, 'name');
            }
        } else {
            foreach ($results as $parent_name => $rows) {
                foreach ($rows as $row) {
                    $child = $this->populateItem($row);
                    $items[$parent_name][$child->name] = $child;
                }
            }
        }
        return $item !== null ? reset($items) : $items;
    }

    /**
     * Remove children from item
     * @param  Item $item
     * @return void
     */
    public function removeItemChildren($item)
    {
        $this->childrenQuery()
            ->where('parent_id', '=', $item->id)
            ->delete();
    }

    /**
     * Get user assignments.
     * From one user as indexed array or from all users as array keyed by user id.
     * @param int|null $user_id
     * @param bool     $onlyNames
     * @return array
     */
    public function getAssignments($user_id = null, $onlyNames = false)
    {
        $query = $this->assignsQuery('asg')
            ->join(Item::DB_TABLE_ITEMS . ' AS it', 'asg.item_id', '=', 'it.id');
        if ($user_id !== null) {
            $query->where('asg.user_id', '=', $user_id)->take(1);
        }
        $results = $query->get(['it.*', 'asg.user_id'])->groupBy('user_id');
        if (empty($results)) {
            return [];
        }
        $items = [];
        $names = [];
        foreach ($results as $userId => $rows) {
            $items[$userId] = Arr::pluck($rows, 'name');
            $names = array_merge($names, $items[$userId]);
        }
        if (!$onlyNames) {
            $itemsAll = $this->getItemsInternal($names);
            foreach ($items as $userId => $names) {
                $items[$userId] = $this->findDataConditionally(['name' => $names], $itemsAll, true);
            }
        }
        return $user_id !== null && !empty($items) ? reset($items) : $items;
    }

    /**
     * Add user assignment
     * @param  Item     $item
     * @param  int|null $user_id
     * @return bool
     */
    public function addAssignment($item, $user_id)
    {
        $exist = $this->assignsQuery()
            ->where('user_id', '=', $user_id)
            ->where('item_id', '=', $item->id)
            ->exists();
        if ($exist) {
            return false;
        }
        $record = [
            'user_id' => $user_id,
            'item_id' => $item->id,
        ];
        return $this->assignsQuery()->insert($record);
    }

    /**
     * Remove assignment (from one user or all)
     * @param  Item     $item
     * @param  int|null $user_id
     * @return void
     */
    public function removeAssignment($item, $user_id = null)
    {
        $query = $this->assignsQuery();
        if ($user_id !== null) {
            $query->where('user_id', '=', $user_id);
        }
        $query->where('item_id', '=', $item->id)
            ->delete();
    }

    /**
     * Remove all user assignments
     * @param  int $user_id
     * @return void
     */
    public function removeAssignments($user_id)
    {
        $this->assignsQuery()
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
        $query = $this->itemsQuery();
        if ($names !== null) {
            $query->whereIn('name', $names);
        }
        if ($type !== null) {
            $query->where('type', '=', $type);
        }
        $result = $query->orderBy('name')
            ->get()->toArray();
        $items = [];
        foreach ($result as $row) {
            $item = $this->populateItem($row);
            $items[$item->name] = $item;
        }
        return $items;
    }

    /**
     * Get query builder for items table
     * @param  string|null $alias
     * @return \Illuminate\Database\Query\Builder
     */
    protected function itemsQuery($alias = null)
    {
        $table = Item::DB_TABLE_ITEMS . ($alias !== null ? ' AS ' . $alias : '');
        return $this->newQuery($table);
    }

    /**
     * Get query builder for assigns table
     * @param  string|null $alias
     * @return \Illuminate\Database\Query\Builder
     */
    protected function assignsQuery($alias = null)
    {
        $table = Item::DB_TABLE_ASSIGNS . ($alias !== null ? ' AS ' . $alias : '');
        return $this->newQuery($table);
    }

    /**
     * Get query builder for children table
     * @param  string|null $alias
     * @return \Illuminate\Database\Query\Builder
     */
    protected function childrenQuery($alias = null)
    {
        $table = Item::DB_TABLE_CHILDREN . ($alias !== null ? ' AS ' . $alias : '');
        return $this->newQuery($table);
    }

    /**
     * @param  string $table
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newQuery($table)
    {
        return $this->db->table($table);
    }
}