<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestOrder extends Model
{
    protected $fillable = ['total_price', 'guest_name','buyer_id','status','coupon_code'];
    protected $table = 'guest_orders';
//    public function GuestOrder() {
//        return $this->belongsToMany(MenuItem::class, 'order_menu_items')->withPivot('quantity');
//
//}
public function menuItems()
{
    return $this->hasMany(GuestOrderMenuItem::class, 'order_id');
}

    public function getCreatedAtForHumansAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->diffForHumans();
    }
}
