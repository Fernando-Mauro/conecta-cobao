<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'campus_id',
        'active'
    ];

    public $timestamps = true;
    public function groupTeachers()
    {
        return $this->hasMany(GroupTeacher::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'group_teacher')
                    ->withPivot('subject_id'); // Incluye la materia en el pivot
    }
}
