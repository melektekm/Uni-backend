<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMenuItem extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'menu_item_id' , 'quantity'];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

}
