<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ScheduleRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_name',
        'course_code',
        'classroom',
        'labroom',
        'class_days',
        'lab_days',
        'lab_instructor',
        'class_instructor',
        'schedule_type',
        'requested_by',
    ];

    public function user()
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }
}
