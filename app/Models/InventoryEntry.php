<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryEntry extends Model
{

    use HasFactory;
    protected $fillable = ['name', 'measured_in', 'quantity', 'price_per_item', 'price_word','quantity_left',];
    protected $table = 'inventory_entries';
}
