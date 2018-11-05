<?php

namespace DigitSoft\LaravelRbac\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Interface Item
 * @package DigitSoft\LaravelRbac\Contracts
 * @property string $id
 * @property string $name
 * @property string $title
 * @property string $description
 * @property array  $children
 * @method int type()
 */
interface Item extends Arrayable, Jsonable
{
    const TYPE_PERMISSION   = 1;
    const TYPE_ROLE         = 2;

    const DB_TABLE_ITEMS    = 'rbac_items';
    const DB_TABLE_CHILDREN = 'rbac_children';
    const DB_TABLE_ASSIGNS  = 'rbac_assigns';

    /**
     * Get the instance as an array.
     *
     * @param  bool $withGuarded
     * @return array
     */
    public function toArray($withGuarded = false);

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @param  bool $withGuarded
     * @return string
     */
    public function toJson($options = 0, $withGuarded = false);

    /**
     * Fill item with data
     * @param  array $attributes
     * @return void
     */
    public function fill(array $attributes);
}