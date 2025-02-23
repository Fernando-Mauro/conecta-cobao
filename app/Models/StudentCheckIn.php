<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCheckIn extends Model
{
    use HasFactory;
    protected $table = 'student_check_in'; // Nombre de la tabla

    protected $fillable = [
        'student_id',
    ];

    public $timestamps = true;

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
