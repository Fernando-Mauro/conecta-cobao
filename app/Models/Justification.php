<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Justification extends Model
{
    use HasFactory;
    protected $table = 'justifications';

    protected $fillable = [
        'student_id',
        'tutor_id',
        'start_date',
        'end_date',
        'active',
        'approved',
        'files_names',
        'campus_id'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }
}
