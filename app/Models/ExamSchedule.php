<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    use HasFactory;
    protected $table = 'exam_schedules';
    protected $primaryKey = 'exam_id';
    protected $fillable = [
        'exam_id',
        'class_id',
        'timing',
    ];
}
