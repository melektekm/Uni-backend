<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    use HasFactory;
    protected $table = 'class_schedules';
    protected $primaryKey = 'class_id';
    protected $fillable = [
        'class_id',
        'timing',
        'location',
    ];
}
