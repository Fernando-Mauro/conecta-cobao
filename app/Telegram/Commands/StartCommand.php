<?php

namespace App\Telegram\Commands;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Inicie el registro de su número con este comando';
    protected array $aliases = ['comenzar']; 

    public function handle()
    {
        $keyboard = Keyboard::make()
            ->inline()
            ->row(
                [
                    Keyboard::inlineButton(['text' => 'Registrar número', 'callback_data' => 'registrar']),
                    Keyboard::inlineButton(['text' => 'Borrar número', 'callback_data' => 'borrar'])
                ]
            );

        $this->replyWithMessage([
            'text' => 'Hola! Bienvenido al chat del cobao, ¿Qué desea hacer?',
            'reply_markup' => $keyboard
        ]);
    }
}
