<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\parents;
class student extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'student_code',
        'budget',
        'QRCode',
        'parent_id',
        'age',
    ];

    public function parent()
    {
        return $this->belongsTo(Parents::class, 'parent_id');
    }

    
    public function wristband()
    {
        return $this->hasOne(wristband::class);
    }

    public function seller()
    {
        return $this->hasOne(seller::class);
    }

    public function product()
    {
        return $this->hasOne(product::class);
    }

    public function medical()
    {
        return $this->hasOne(medical::class);
    }

}
