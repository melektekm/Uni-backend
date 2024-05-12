<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $table = 'students';

    protected $primaryKey = 'student_id';

    protected $fillable = [
        'student_id',
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'student_password',
    ];
}
