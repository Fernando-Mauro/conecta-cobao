<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'body',
        'description',
        'active'
    ];


    public $timestamps = true;

    public function historyMessages():HasMany{ 
        return $this->hasMany(HistoryMessage::class, 'message_id' , 'id');
    }
}