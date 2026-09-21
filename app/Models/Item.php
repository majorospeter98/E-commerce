<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Item extends Model
{
    protected $guarded = [];
    protected $casts = [
    'size' => 'array',
];
   public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_item');
    }
}
