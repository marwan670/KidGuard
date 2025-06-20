<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wristband extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
    ];
    
    public function student()
    {
        return $this->belongsTo(student::class);
    }

}
