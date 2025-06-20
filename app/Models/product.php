<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'product_detials',
        'price',
        
    ];

    public function product_restriction()
    {
        return $this->hasOne(product_restriction::class);
    }
}
