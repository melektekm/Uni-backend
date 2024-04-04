<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'account_id',
        'type',
        'amount',
    ];
    protected $table = 'transactions';

    public function getCreatedAtForHumansAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->diffForHumans();
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
