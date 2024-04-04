<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'employee_id',
        'balance',
        'status',
        'last_deposit_date',
    ];
    protected $table = 'accounts';
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

}

