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
 * @mixin \Eloquent
 * @mixin \DigitSoft\LaravelRbac\Models\Permission
 */
interface Item extends Arrayable, Jsonable
{
    const TYPE_PERMISSION   = 1;
    const TYPE_ROLE         = 2;

    const DB_TABLE_ITEMS    = 'rbac_items';
    const DB_TABLE_CHILDREN = 'rbac_children';
    const DB_TABLE_ASSIGNS  = 'rbac_assigns';
}