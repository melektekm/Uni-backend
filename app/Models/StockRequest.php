<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{


    use HasFactory;

    protected $fillable = ['name', 'quantity', 'measured_in', 'requested_by', 'approved_by','item_id', 'group_id'];

    protected $table = 'stock_requests';
}
