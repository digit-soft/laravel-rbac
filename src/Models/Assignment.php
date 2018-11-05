<?php

namespace DigitSoft\LaravelRbac\Models;

use App\Models\User;
use DigitSoft\LaravelRbac\Contracts\Item as ItemContract;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Assignment
 * @package DigitSoft\LaravelRbac\Models
 * @property int                  $id
 * @property int                  $user_id
 * @property int                  $item_id
 * @property-read User            $user
 * @property-read Permission|Role $item
 * @mixin \Eloquent
 */
class Assignment extends Model implements ItemContract
{
    public $timestamps = false;

    protected $table = ItemContract::DB_TABLE_ASSIGNS;

    protected $fillable = ['user_id', 'item_id'];

    /**
     * Get user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        $userClass = config('rbac.user_model');
        return $this->belongsTo($userClass, 'user_id', 'id', 'user');
    }

    /**
     * Get item
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Permission::class, 'item_id', 'id', 'item');
    }
}