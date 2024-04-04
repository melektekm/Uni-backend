<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequest extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'measured_in', 'quantity', 'price_per_item', 'price_word'];

    protected $table = 'inventory_requests';
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d h:i:s A');
    }
}
