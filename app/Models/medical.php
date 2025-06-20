<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class medical extends Model
{
    use HasFactory;
    protected $fillable = [
        'medical_name',
        'medical_file',
        'student_id',
    ];
    
    public function student()
    {
        return $this->belongsTo(student::class);
    }
    
    public function product_restriction()
    {
        return $this->hasOne(product_restriction::class);
    }

}
