<?php

namespace DigitSoft\LaravelRbac\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Interface Permission
 * @package DigitSoft\LaravelRbac\Contracts
 * @property string $id
 * @property string $name
 * @property string $title
 * @property string $description
 * @property array  $children
 */
interface Permission extends Arrayable, Jsonable
{
    /**
     * Get the instance as an array.
     *
     * @param bool $withGuarded
     * @return array
     */
    public function toArray($withGuarded = false);

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @param bool $withGuarded
     * @return string
     */
    public function toJson($options = 0, $withGuarded = false);
}