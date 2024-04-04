<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequestEntry extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_request_start_id', 'purchase_request_end_id', 'submitted_items_start_id', 'submitted_items_end_id',
    'recommendations','request_approved_by','entry_approved_by','requested_by','total_price_request','total_price_entry','request_status','returned_amount','file_path'];
   
    public function requestedBy()
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }
    public function entryApprovedBy()
    {
        return $this->belongsTo(Employee::class, 'entry_approved_by');
    }
    protected $table = 'inventory_request_entries';

}
