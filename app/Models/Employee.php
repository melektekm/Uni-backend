<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = ['id','name', 'department', 'email', 'role', 'password','status'];

    protected $table = 'employees';
    protected $primaryKey = 'id';

    protected static function booted()
    {
        static::creating(function ($employee) {

            $employee->id = str_pad(mt_rand(1001, 9999), 4, );
        });
    }

    public function isAdmin()
    {
        return ($this->role === 'admin' || $this->role === 'cashier'|| $this->role ==='storeKeeper' || $this->role === 'communittee_admin' || $this->role === 'cafeManager_admin' );
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function guestOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GuestOrder::class,'buyer_id');
    }

    public function searchEmployeeByNameM($query, $term)
    {

        return $query->whereRaw("SOUNDEX(name) = SOUNDEX(?)", [$term]);
    }
    public function department()
{
    return $this->belongsTo(Department::class, 'department');
}

    public function account()
    {
        return $this->hasOne(Account::class);
    }

}
