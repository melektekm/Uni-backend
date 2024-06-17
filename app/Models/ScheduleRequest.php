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
        'schedule_type',
        'examDate',
        'examTime',
        'examRoom',
        'examiner',
        'yearGroup',
        'year',
        'status', 
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_code', 'course_code');
    }


}
