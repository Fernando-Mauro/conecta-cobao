<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'enrollment',
        'name',
        'phone',
        'curp',
        'group_id',
        'campus_id',
        'user_id'
    ];


    public $timestamps = true;

    // The student have  one user 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function checkIns(): HasMany
    {
        return $this->hasMany(StudentCheckIn::class, 'student_id', 'id');
    }

    public function checkOuts(): HasMany
    {
        return $this->hasMany(StudentCheckOut::class, 'student_id', 'id');
    }

    public function tutorStudent(): HasOne
    {
        return $this->hasOne(TutorStudent::class, 'student_id', 'id');
    }

    public function reports(){
        return $this->hasMany(Report::class, 'student_id', 'id');
    }
    
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id', 'id');
    }
}