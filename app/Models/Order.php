<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use App\Models\Item;
class Order extends Model
{
    protected $guarded = [];

    public function users(){
        return $this->belongsTo(User::class);
    }
      public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'order_items')
        ->withPivot('size', 'quantity');
    }
}
