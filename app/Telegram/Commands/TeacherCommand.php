<?php

namespace App\Telegram\Commands;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class TeacherCommand extends Command
{
    protected string $name = 'teacher';
    protected string $description = 'Inicie el registro como docente';
    protected array $aliases = ['maestro', 'docente']; 

    public function handle()
    {
        $this->replyWithMessage([
            'text' => 'Hola! Docente',
        ]);
    }
}