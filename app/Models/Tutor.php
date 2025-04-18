<?php

namespace App\Models;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Tutor extends Model
{
    use HasFactory;
    protected $table = 'tutors'; // Nombre de la tabla

    protected $fillable = [
        'name',
        'phone',
        'telegram_chat_id',
        'campus_id',
        'active',
        'user_id'
    ];


    public $timestamps = true;

    public function activeStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'tutor_students', 'tutor_id', 'student_id')->where('students.active', true);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
