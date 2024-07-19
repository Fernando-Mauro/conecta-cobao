<?php

namespace App\Telegram\Commands;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class TutorCommand extends Command
{
    protected string $name = 'tutor';
    protected string $description = 'Inicie el registro como padre/madre de familia';
    protected array $aliases = ['padre', 'madre']; 

    public function handle()
    {
        $this->replyWithMessage([
            'text' => 'Hola! Padre',
        ]);
    }
}