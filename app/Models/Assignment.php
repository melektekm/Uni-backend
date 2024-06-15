<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class Assignment extends Model
{
    use HasFactory;
    protected $table = 'assignment';
    protected $primaryKey = 'id';
    protected $fillable = [
        'course_code',
        'course_name',
        'assignmentName',
        'assignmentDescription',
        'dueDate',
        'file',
        'status',
    ];
    public function students()
    {
        return $this->belongsToMany(Student::class, 'submitted_assignments', 'assignment_id', 'student_id')
            ->withPivot('status');
    }
}
