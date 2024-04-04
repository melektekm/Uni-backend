<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments'; // Specify the table name if it's different from the model's pluralized name.

    protected $fillable = [
        'name',
     
        'parent_id',
    ];

    

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function subDepartments()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DepartmentOrder::class,'department_id');
    }
}
