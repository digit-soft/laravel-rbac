<?php

namespace DigitSoft\LaravelRbac\Storages;

use DigitSoft\LaravelRbac\Contracts\Item;
use DigitSoft\LaravelRbac\Contracts\Storage;
use DigitSoft\LaravelRbac\Misc\VarDumper;
use DigitSoft\LaravelRbac\Traits\StorageHelpers;
use Illuminate\Filesystem\Filesystem;

/**
 * Class PhpFileStorage
 * @package DigitSoft\LaravelRbac\Storages
 */
class PhpFileStorage implements Storage
{
    use StorageHelpers;

    /**
     * Items file path
     * @var string
     */
    protected $itemsFile;
    /**
     * Assigns file path
     * @var string
     */
    protected $assignsFile;
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var array|null
     */
    protected $items;
    /**
     * @var array|null
     */
    protected $assignments;
    /**
     * @var array|null
     */
    protected $itemsArray;

    /**
     * PhpFileStorage constructor.
     * @param Filesystem $files
     * @param string     $itemsFile
     * @param string     $assignsFile
     */
    public function __construct(Filesystem $files, $itemsFile, $assignsFile)
    {
        $this->files = $files;
        $this->itemsFile = $itemsFile;
        $this->assignsFile = $assignsFile;
    }

    /**
     * Get all items
     * @param int|null $type
     * @return Item[]
     */
    public function getItems($type = null)
    {
        $conditions = $type !== null ? ['type' => $type] : [];
        return $this->getItemsInternal($conditions);
    }

    /**
     * Get item by name
     * @param string $name
     * @return Item|null
     */
    public function getItem($name)
    {
        $conditions = ['name' => $name];
        $items = $this->getItemsInternal($conditions);
        return !empty($items) ? reset($items) : null;
    }

    /**
     * Save item to storage
     * @param Item $item
     * @return bool
     */
    public function saveItem($item)
    {
        if ($this->itemsArray === null) {
            $this->loadItemsAndChildren();
        }
        $itemArray = $item->toArray(true);
        $itemArray['children'] = $this->itemsArray[$item->name]['children'] ?? [];
        $this->itemsArray[$item->name] = $itemArray;
        $this->writeToFile($this->itemsFile, $this->itemsArray);
        $this->reset();
        return true;
    }

    /**
     * Remove item from storage
     * @param Item $item
     * @return void
     */
    public function removeItem($item)
    {
        if ($this->itemsArray === null) {
            $this->loadItemsAndChildren();
        }
        // Remove from all items children
        $this->removeItemChild($item);
        // Remove from assignments
        $this->removeAssignment($item);

        if (isset($this->itemsArray[$item->name])) {
            unset($this->itemsArray[$item->name]);
            $this->writeToFile($this->itemsFile, $this->itemsArray);
            $this->reset();
        }
    }

    /**
     * Add item child
     * @param Item $child
     * @param Item $item
     * @return bool
     */
    public function addItemChild($child, $item)
    {
        if ($this->itemsArray === null) {
            $this->loadItemsAndChildren();
        }
        if (!isset($this->itemsArray[$item->name]['children'])
            || !in_array($child->name, $this->itemsArray[$item->name]['children'])) {
            $this->itemsArray[$item->name]['children'][] = $child->name;
            sort($this->itemsArray[$item->name]['children']);
            $this->writeToFile($this->itemsFile, $this->itemsArray);
            $this->reset();
        }
        return true;
    }

    /**
     * Remove child from item or from all items
     * @param Item|null $child
     * @param Item|null $item
     * @return void
     */
    public function removeItemChild($child, $item = null)
    {
        if ($this->itemsArray === null) {
            $this->loadItemsAndChildren();
        }
        if ($item !== null && !isset($this->itemsArray[$item->name])) {
            return;
        }
        if ($item !== null) {
            $itemKeys = [$item->name];
        } else {
            $itemKeys = array_keys($this->itemsArray);
        }
        $changed = false;
        foreach ($itemKeys as $itemKey) {
            $this->itemsArray[$itemKey]['children'] = $this->itemsArray[$itemKey]['children'] ?? [];
            $key = array_search($child->name, $this->itemsArray[$itemKey]['children']);
            if ($key !== false) {
                unset($this->itemsArray[$itemKey]['children'][$key]);
                sort($this->itemsArray[$itemKey]['children']);
                $changed = true;
            }
        }
        if ($changed) {
            $this->writeToFile($this->itemsFile, $this->itemsArray);
            $this->reset();
        }
    }

    /**
     * Get item children
     * @param Item|null $item
     * @param bool      $onlyNames
     * @return Item[]|string[]
     */
    public function getItemChildren($item = null, $onlyNames = false)
    {
        if ($this->itemsArray === null) {
            $this->loadItemsAndChildren();
        }
        if ($item !== null) {
            if (!empty($this->itemsArray[$item->name]['children'])) {
                $names = $this->itemsArray[$item->name]['children'];
                return $onlyNames ? $names : $this->getItemsInternal(['name' => $names]);
            }
        } else {
            $children = [];
            if (!$onlyNames) {
                foreach ($this->itemsArray as $itemName => $itemArray) {
                    $children[$itemName] = !empty($itemArray['children'])
                        ? $this->getItemsInternal(['name' => $itemArray['children']]) : [];
                }
            } else {
                foreach ($this->itemsArray as $itemName => $itemArray) {
                    $children[$itemName] = $itemArray['children'] ?? [];
                }
            }
        }
        return [];
    }

    /**
     * Remove children from item
     * @param Item $item
     * @return void
     */
    public function removeItemChildren($item)
    {
        if ($this->itemsArray === null) {
            $this->loadItemsAndChildren();
        }
        if (isset($this->itemsArray[$item->name])) {
            $this->itemsArray[$item->name]['children'] = [];
            $this->writeToFile($this->itemsFile, $this->itemsArray);
            $this->reset();
        }
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
        if ($this->assignments === null) {
            $this->loadAssignments();
        }
        if ($user_id !== null) {
            $names = $this->assignments[$user_id] ?? [];
            return !$onlyNames ? $this->getItemsInternal(['name' => $names]) : $names;
        } elseif (!empty($this->assignments)) {
            if ($onlyNames) {
                return $this->assignments;
            }
            $items = [];
            foreach ($this->assignments as $userId => $names) {
                if (empty($names)) {
                    $items[$user_id] = [];
                    continue;
                }
                $items[$user_id] = $this->getItemsInternal(['name' => $names]);
            }
            return $items;
        }
        return [];
    }

    /**
     * Add user assignment
     * @param Item     $item
     * @param int|null $user_id
     * @return bool
     */
    public function addAssignment($item, $user_id)
    {
        if ($this->assignments === null) {
            $this->loadAssignments();
        }
        if (!isset($this->assignments[$user_id]) || !in_array($item->name, $this->assignments[$user_id])) {
            $this->assignments[$user_id] = $this->assignments[$user_id] ?? [];
            $this->assignments[$user_id][] = $item->name;
            sort($this->assignments[$user_id]);
            $this->writeToFile($this->assignsFile, $this->assignments);
            $this->reset();
            return true;
        }
        return false;
    }

    /**
     * Remove assignment (from one user or all)
     * @param Item $item
     * @param int|null $user_id
     * @return void
     */
    public function removeAssignment($item, $user_id = null)
    {
        if ($this->assignments === null) {
            $this->loadAssignments();
        }
        $userKeys = $user_id !== null ? [$user_id] : array_keys($this->assignments);
        $changed = false;
        foreach ($userKeys as $userKey) {
            if (!empty($this->assignments[$userKey]) && ($itemKey = array_search($item->name, $this->assignments[$userKey])) !== false) {
                unset($this->assignments[$userKey][$itemKey]);
                sort($this->assignments[$userKey]);
                $changed = true;
            }
        }
        if ($changed) {
            $this->writeToFile($this->assignsFile, $this->assignments);
            $this->reset();
        }
    }

    /**
     * Remove all user assignments
     * @param int $user_id
     * @return void
     */
    public function removeAssignments($user_id)
    {
        if (empty($this->assignments[$user_id])) {
            return;
        }
        $this->assignments[$user_id] = [];
        $this->writeToFile($this->assignsFile, $this->assignments);
        $this->reset();
    }


    /**
     * @param array $conditions
     * @return array|null
     */
    protected function getItemsInternal($conditions = [])
    {
        if ($this->items === null) {
            $this->loadItemsAndChildren();
        }
        return !empty($conditions)
            ? $this->findDataConditionally($conditions, $this->items, true)
            : $this->items;
    }

    /**
     * @param int|null $user_id
     * @return array
     */
    protected function getAssignsInternal($user_id = null)
    {
        if ($this->assignments === null) {
            $this->loadAssignments();
        }
        if ($user_id === null) {
            return $this->assignments;
        }
        return $this->assignments[$user_id] ?? [];
    }

    /**
     * Load items and children from file
     */
    protected function loadItemsAndChildren()
    {
        $this->itemsArray = $this->readFromFile($this->itemsFile);
        $this->items = [];
        foreach ($this->itemsArray as $name => $item) {
            $this->normalizeItemArray($item, $name, true);
            $this->items[$name] = $this->populateItem($item);
        }
    }

    /**
     * Load assignments from file
     */
    protected function loadAssignments()
    {
        $this->assignments = $this->readFromFile($this->assignsFile);
    }

    /**
     * Reset cached items
     */
    protected function reset()
    {
        $this->itemsArray = null;
        $this->items = null;
        $this->assignments = null;
    }

    /**
     * Write data to file
     * @param string $filePath
     * @param array  $data
     */
    protected function writeToFile($filePath, $data = [])
    {
        $dataStr = VarDumper::export($data);
        $fileStr =<<<EOF
<?php

return $dataStr;

EOF;
        $this->files->put($filePath, $fileStr);
    }

    /**
     * Read data from PHP file
     * @param string $filePath
     * @return array
     */
    protected function readFromFile($filePath)
    {
        if ($this->files->exists($filePath)) {
            return $this->files->getRequire($filePath);
        }
        return [];
    }
}