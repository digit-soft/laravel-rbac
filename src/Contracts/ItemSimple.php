<?php

namespace DigitSoft\LaravelRbac\Contracts;

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
interface ItemSimple extends Item
{
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