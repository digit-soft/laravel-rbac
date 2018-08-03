<?php

namespace DigitSoft\LaravelRbac;

use DigitSoft\LaravelRbac\Contracts\Permission as PermissionContract;

/**
 * Class Permission
 * @package DigitSoft\LaravelRbac
 */
class Permission implements PermissionContract
{
    /**
     * @var int|null
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $description;
    /**
     * @var array
     */
    public $children;
    /**
     * @var \ReflectionClass[]
     */
    protected static $_reflections = [];
    /**
     * @var array
     */
    protected $guarded = ['children'];

    /**
     * Permission constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->fill($config);
    }

    /**
     * @inheritdoc
     */
    public function toArray($withGuarded = false)
    {
        $reflection = $this->getReflection();
        $data = [];
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC &~ \ReflectionProperty::IS_STATIC) as $property) {
            $name = $property->getName();
            if (!$withGuarded && in_array($name, $this->guarded)) {
                continue;
            }
            $data[$name] = $this->{$name};
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function toJson($options = 0, $withGuarded = false)
    {
        return json_encode($this->toArray($withGuarded), $options);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get item type
     * @return int
     */
    public function type()
    {
        return static::TYPE_PERMISSION;
    }

    /**
     * @inheritdoc
     */
    public function fill($data = [])
    {
        $reflection = $this->getReflection();
        foreach ($data as $key => $value) {
            if (
                property_exists($this, $key)
                && ($prop = $reflection->getProperty($key)) !== null
                && $prop->isPublic()
                && !$prop->isStatic()) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @return \ReflectionClass
     */
    protected function getReflection()
    {
        $class = get_called_class();
        if (!isset(static::$_reflections[$class])) {
            static::$_reflections[$class] = new \ReflectionClass($class);
        }
        return static::$_reflections[$class];
    }
}