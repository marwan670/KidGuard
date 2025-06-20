<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_restriction extends Model
{
    use HasFactory;
    protected $table = 'product_restrictions'; 
     protected $fillable = [
        'medical_id',
        'product_id',
    ];
    public function medical()
    {
        return $this->belongsTo(medical::class);
    }
    public function product()
    {
        return $this->belongsTo(product::class);
    }
}
