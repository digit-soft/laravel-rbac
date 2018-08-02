<?php

namespace DigitSoft\LaravelRbac\Sources;

use DigitSoft\LaravelRbac\Contracts\ItemSource;
use DigitSoft\LaravelRbac\Contracts\Permission;
use DigitSoft\LaravelRbac\Contracts\Role;
use DigitSoft\LaravelRbac\Traits\ItemSourceHelpers;
use Illuminate\Filesystem\Filesystem;

/**
 * Class PhpFileSource
 * @package DigitSoft\LaravelRbac\Sources
 */
class PhpFileSource implements ItemSource
{
    use ItemSourceHelpers;
    /**
     * File with items declarations
     * @var string
     */
    protected $itemsFile;
    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * PhpFileSource constructor.
     * @param Filesystem $files
     * @param string     $itemsFile
     */
    public function __construct(Filesystem $files, $itemsFile)
    {
        $this->files = $files;
        $this->itemsFile = $itemsFile;
    }

    /**
     * Get all items
     * @param int|null $type
     * @return Permission[]|Role[]
     */
    public function getItems($type = null)
    {
        return $this->getItemsInternal($type);
    }

    /**
     * Get item
     * @param string $name
     * @return Permission|Role
     */
    public function getItem($name)
    {
        $items = $this->getItems();
        return $items[$name] ?? null;
    }

    /**
     * Save item
     * WARNING! Not supported! You must edit file!
     * @param Permission|Role $item
     * @return bool
     */
    public function saveItem($item)
    {
        return true;
    }

    /**
     * Remove item
     * WARNING! Not supported! You must edit file!
     * @param string|Permission|Role $name
     * @param int                    $type
     * @return bool
     */
    public function removeItem($name, $type = self::TYPE_PERMISSION)
    {
        return true;
    }

    /**
     * Add child to item
     * WARNING! Not supported! You must edit file!
     * @param Permission|Role $item
     * @param Permission|Role $child
     * @return $this
     */
    public function addChild($item, $child)
    {
        return $this;
    }

    /**
     * Remove item child
     * WARNING! Not supported! You must edit file!
     * @param Permission|Role $item
     * @param Permission|Role $child
     * @return $this
     */
    public function removeChild($item, $child)
    {
        return $this;
    }

    /**
     * Remove all children
     * WARNING! Not supported! You must edit file!
     * @param Permission|Role $item
     * @return $this
     */
    public function removeChildren($item)
    {
        return $this;
    }

    /**
     * Get item children
     * @param string $parentName
     * @param int    $parentType
     * @param bool   $recursive
     * @return mixed
     */
    public function getChildren($parentName, $parentType = self::TYPE_PERMISSION, $recursive = false)
    {
        if ($recursive) {
            $children = $this->getChildrenInternal($parentName);
        } else {
            $item = $this->getItem($parentName);
            $children = $item->children;
        }
        return !empty($children) ? $this->getItemsInternal(null, $children) : [];
    }

    /**
     * Load items from source as array with children
     * @return array
     */
    protected function loadSourceItemArray()
    {
        return $this->loadFileData($this->itemsFile);
    }

    /**
     * Load file content (array) from filesystem
     * @param string $filePath
     * @return array|mixed
     */
    protected function loadFileData($filePath)
    {
        if ($this->files->exists($filePath)) {
            return include $filePath;
        }
        return [];
    }
}