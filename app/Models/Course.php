<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $table = 'courses';
    protected $primaryKey = 'course_code';
    protected $fillable = [
        'course_code',
        'course_name',
        'course_description',
        'credit_hours',
        'year',
        'semester',
    ];
}
