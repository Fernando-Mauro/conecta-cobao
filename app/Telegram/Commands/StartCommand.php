<?php

namespace App\Telegram\Commands;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class StartCommand extends Command
{
    public string $name = 'start';
    public string $description = 'Inicie el registro de su número con este comando';

    public function handle()
    {
        Log::channel('daily')->info('Recibiendo un comando start D:');

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
