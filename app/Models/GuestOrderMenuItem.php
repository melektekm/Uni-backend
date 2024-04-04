<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestOrderMenuItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'menu_item_id' , 'quantity'];
    protected $table = 'guest_order_menu_items';

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function order()
    {
        return $this->belongsTo(GuestOrder::class, 'order_id');
    }
}
