<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;
    protected $table = '_assignment';
    protected $primaryKey = 'id';
    protected $fillable = [
        'course_id',
        'ass_name',
        'ass_description',
        'due_date',
        'file_path',
        'status',
    ];
}
