<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class notification extends Model
{
    use HasFactory;
    protected $table = 'notifications'; 
    protected $fillable = [
        'parent_id',
        'admin_id',
        'message',
    ];
    public function admin()
    {
        return $this->belongsTo(admin::class);
    }
    public function parent()
    {
        return $this->belongsTo(parents::class);
    }
}
