<?php

namespace DigitSoft\LaravelRbac\Contracts;

/**
 * Interface Permission
 * @package DigitSoft\LaravelRbac\Contracts
 * @property string $id
 * @property string $name
 * @property string $title
 * @property string $description
 * @property array  $children
 */
interface Permission extends ItemSimple
{
}