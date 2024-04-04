<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentOrder extends Model
{



        use HasFactory;
    
    
        protected $fillable = ['file_path', 'department_id','lunch_price_per_person','buyer_id','refreshment_price_per_person','refreshment_per_day','number_of_people','number_of_days', 'serving_date_start','serving_date_end',];
    
        public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
        {
            return $this->belongsTo(Department::class,'department_id');
        }
        public function buyer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
        {
            return $this->belongsTo(Employee::class,'buyer_id');
        }
        
    
    }
    
