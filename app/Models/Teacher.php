<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'user_id',
        'active',
        'campus'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
