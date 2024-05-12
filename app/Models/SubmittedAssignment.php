<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmittedAssignment extends Model
{
    protected $table = 'submitted_assignments'; // Table name in the database
    protected $primaryKey = 'id'; // Primary key column in the table
    protected $fillable = [];

    // Define any relationships with other models, if applicable
    public function assignment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
