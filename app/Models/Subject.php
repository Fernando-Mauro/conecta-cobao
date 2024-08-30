<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'campus_id',
        'level_id',
    ];
    public function groupTeachers()
    {
        return $this->hasMany(GroupTeacher::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}
