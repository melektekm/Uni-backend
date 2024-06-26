<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ScheduleRequest extends Model
{
    use HasFactory;
    protected $table = 'schedule_requests';
    protected $fillable = [
        'course_name',
        'course_code',
        'classroom',
        'labroom',
        'classDays',
        'labDays',
        'labInstructor',
        'classInstructor',
        'scheduleType',
        'status',
    ];




}
