<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_selected extends Model
{
    use HasFactory;
    protected $table = 'Product_selected'; 
     protected $fillable = [
        'student_id',
        'seller_id',
        'product_id',
        'status',
    ];
    public function student()
    {
        return $this->belongsTo(student::class);
    }
    public function seller()
    {
        return $this->belongsTo(seller::class);
    }
    public function product()
    {
        return $this->belongsTo(product::class);
    }
}
