<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'image_url', 'price_for_guest', 'price_for_employee','price_for_department','meal_type', 'is_fasting', 'available_amount', 'is_available','is_drink'];

    public function orders() {
        return $this->belongsToMany(Order::class, 'order_menu_items')->withPivot('quantity');
    }
    public function guestOrderItems()
    {
        return $this->hasMany(GuestOrderMenuItem::class, 'menu_item_id');
    }
    public function guestOrders() {
        return $this->belongsToMany(GuestOrder::class, 'guest_order_menu_items', 'menu_item_id', 'order_id')->withPivot('quantity');
    }
    public function searchFuzzy($query, $term) {
        return $query->whereRaw("SOUNDEX(name) = SOUNDEX(?)", [$term]);
    }
}

