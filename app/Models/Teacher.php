<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'user_id',
        'active',
        'campus_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function groupTeachers()
    {
        return $this->hasMany(GroupTeacher::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_teacher')
                    ->withPivot('subject_id');
    }
}
