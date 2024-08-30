<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use HasFactory;
    protected $table = 'campus';
    protected $fillable = [
        'name',
        'campus_number',
        'addres',
        'city',
        'school_id',
        'active'
    ];

    public $timestamps = true;
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function levels()
    {
        return $this->hasMany(Level::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
