<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;


    protected $fillable = ['total_price', 'employee_id','status','cashier_id','coupon_code', ];

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getCreatedAtForHumansAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->diffForHumans();
    }
    public function menuItems() {
            return $this->belongsToMany(MenuItem::class, 'order_menu_items')->withPivot('quantity');

    }

}
