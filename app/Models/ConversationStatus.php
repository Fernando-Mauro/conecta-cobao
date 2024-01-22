<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationStatus extends Model
{
    use HasFactory;
    protected $table = 'conversation_status'; // Nombre de la tabla

    protected $fillable = [
        'chat_id',
        'conversation_state'
    ];


    public $timestamps = true;
}
