<?php

namespace App\Models;

use App\Models\Tutor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TutorStudent extends Model
{
    use HasFactory;
    protected $table = 'tutor_students'; // Nombre de la tabla

    protected $fillable = [
        'tutor_id',
        'student_id'
    ];

    public $timestamps = true;

    public function activeTutor(): HasOne 
    {
        return $this->hasOne(Tutor::class, 'id', 'tutor_id');
    }
}
