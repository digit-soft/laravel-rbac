<?php

namespace DigitSoft\LaravelRbac\Models;

use DigitSoft\LaravelRbac\Contracts\Item as ItemContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class Permission
 * @property int                      $id ID
 * @property int                      $type Item type
 * @property string                   $name Name
 * @property string                   $title Title
 * @property string                   $description Description
 * @property-read Permission[]|Role[] $parents Parents array
 * @property-read Permission[]        $children Children array
 * @property-read Assignment[]        $assignments Assignments array
 * @package DigitSoft\LaravelRbac\Models
 * @method static \Illuminate\Database\Eloquent\Builder whereName($value)
 * @mixin \Eloquent
 */
class Permission extends Model implements ItemContract
{
    protected $table = ItemContract::DB_TABLE_ITEMS;

    protected $fillable = ['name', 'type', 'title', 'description'];

    protected $hidden = ['children'];

    public $timestamps = false;

    /**
     * @inheritdoc
     */
    public static function boot()
    {
        parent::boot();
        $itemType = static::class === Role::class ? ItemContract::TYPE_ROLE : ItemContract::TYPE_PERMISSION;
        static::saving(function ($item) use ($itemType) {
            /** @var Permission|Role $item */
            $item->type = $itemType;
            $item->name = $item->name !== null && trim($item->name) !== ""
                ? $item->name
                : Str::snake(Str::substr($item->title, 0, 100));
        });
        static::addGlobalScope('type', function (Builder $builder) use ($itemType) {
            $builder->where('type', '=', $itemType);
        });
    }

    /**
     * Get children
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function children()
    {
        return $this->belongsToMany(
            static::class,
            ItemContract::DB_TABLE_CHILDREN,
            'parent_id',
            'child_id',
            'id',
            'id'
            )->withoutGlobalScope('type');
    }

    /**
     * Get parents
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function parents()
    {
        return $this->belongsToMany(
            static::class,
            ItemContract::DB_TABLE_CHILDREN,
            'child_id',
            'parent_id',
            'id',
            'id'
        )->withoutGlobalScope('type');
    }

    /**
     * Get assignments
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignments()
    {
        return $this->hasMany(
            Assignment::class,
            'item_id',
            'id'
        )->withoutGlobalScope('type');
    }

    /**
     * Check that permission can be attached to this item
     * @param Permission $permission
     * @return bool
     */
    public function canBeAttached(Permission $permission)
    {
        $children = $this->children()
            ->with(['children.children.children'])
            ->get()
            ->toArray();
        $children = Arr::pluck(static::unpackItems($children), 'id');
        $parents = $this->parents()
            ->with(['parents.parents.parents'])
            ->get()
            ->toArray();
        $parents = Arr::pluck(static::unpackItems($parents), 'id');
        return !in_array($permission->id, $children) && !in_array($permission->id, $parents);
    }

    /**
     * Check that permission can be detached from this item
     * @param Permission $permission
     * @return bool
     */
    public function canBeDetached(Permission $permission)
    {
        return $this->children()
            ->where(Permission::getTable() . '.id', '=', $permission->id)
            ->exists();
    }

    /**
     * Ensure unique name in DB
     * @return string
     */
    public function ensureUniqueName()
    {
        $queryBasic = $this->newQuery()->withoutGlobalScope('type');
        if ($this->id !== null) {
            $queryBasic->where('id', '!=', $this->id);
        }
        $nameBasic = $this->name ?? Str::slug($this->title, '_');
        $nameUnique = $nameBasic;
        $nameSuffix = 1;
        $searching = true;
        while ($searching) {
            $query = clone $queryBasic;
            $exists = $query->where('name', '=', $nameUnique)->exists();
            // 100 tries
            if ($exists && $nameSuffix <= 100) {
                $nameUnique = $nameBasic . '_' . $nameSuffix;
                $nameSuffix++;
            } else {
                $searching = false;
            }
        }
        $this->name = $nameUnique;
        return $nameUnique;
    }

    /**
     * @inheritdoc
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $attributes = (array) $attributes;
        $modelClass = isset($attributes['type']) && $attributes['type'] === ItemContract::TYPE_ROLE ? Role::class : Permission::class;

        $model = $this->newInstance([], true, $modelClass);

        $model->setRawAttributes($attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        $model->fireModelEvent('retrieved', false);

        return $model;
    }


    /**
     * Create a new instance of the given model.
     *
     * @param  array       $attributes
     * @param  bool        $exists
     * @param  string|null $className
     * @return static
     */
    public function newInstance($attributes = [], $exists = false, $className = null)
    {
        $className = $className ?? static::class;
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        /** @var Permission|Role $model */
        $model = new $className((array) $attributes);

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        return $model;
    }

    /**
     * Unpack all items
     * @param Permission[]|Role[] $items
     * @param string              $key
     * @return Permission[]|Role[]
     */
    public static function unpackItems($items, $key = 'children')
    {
        $unpacked = [];
        foreach ($items as $item) {
            $unpacked[] = $item;
            if (!empty($item->{$key})) {
                $unpacked = array_merge($unpacked, static::unpackItems($item->{$key}, $key));
            }
        }
        return $unpacked;
    }

    /**
     * Get item type
     * @return int
     */
    public function type()
    {
        return $this->type;
    }
}