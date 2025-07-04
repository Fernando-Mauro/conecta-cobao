<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'campus_id'
    ];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'level_id', 'id');
    }

}
