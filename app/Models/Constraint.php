<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Constraint extends Model
{
    use HasFactory;

    protected $fillable = [
        'constraint_name',
        'max_num',
        'min_num',
        'start_time',
        'end_time',
        'isclosed',
    ];
    protected $table = 'constraints';

  
}

