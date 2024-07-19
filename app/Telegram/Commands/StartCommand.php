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
        $keyboard = Keyboard::make()->row([
            Keyboard::button(['text' => '/padre  👨']),
        ])->row([
            Keyboard::button(['text' => '/docente  🧑‍🏫']),
        ])->setOneTimeKeyboard(true);

        $this->replyWithMessage([
            'text' => 'Hola! Bienvenido al chat de conecta-t, ¿Eres padre/madre de familia o docente?',
            'reply_markup' => $keyboard
        ]);
    }
}
