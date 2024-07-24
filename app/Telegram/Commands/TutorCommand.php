<?php

namespace App\Telegram\Commands;

use App\Models\ConversationStatus;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TutorCommand extends Command
{
    protected string $name = 'tutor';
    protected string $description = 'Inicie el registro como padre/madre de familia';
    protected array $aliases = ['padre', 'madre'];

    public function handle()
    {
        $update = $this->getUpdate();

        // Not new Message and not callback query
        if (!$update->isType('my_chat_member') && !$update->isType('callback_query')) {
            $keyboard = Keyboard::make()
                ->inline()
                ->row(
                    [
                        Keyboard::inlineButton(['text' => 'Registrar número', 'callback_data' => 'registrar']),
                        Keyboard::inlineButton(['text' => 'Borrar número', 'callback_data' => 'borrar'])
                    ]
                );
            $this->replyWithMessage([
                'text' => 'Hola ' . $update->getMessage()->getText() . '!¿Qué desea hacer?',
                'reply_markup' => $keyboard
            ]);
        }

        if ($update->isType('callback_query')) {
            $callbackData = $update->getCallbackQuery()->getData();

            if ($callbackData === 'registrar') {
                $this->setRegisterState($update);
            } else if ($callbackData === 'borrar') {
                $this->setDeleteState($update);
            }
        }
    }

    private function getConversationStatus($chatId)
    {
        $conversation = ConversationStatus::where('chat_id', $chatId)->first();

        if (!$conversation) {
            // Si no se encuentra un registro, crea uno con un estado inicial (por ejemplo, "none")
            $conversation = new ConversationStatus();
            $conversation->chat_id = $chatId;
            $conversation->conversation_state = null;
            $conversation->enrollment = null;
            $conversation->type_user = "tutor";
            $conversation->save();
        }

        return $conversation;
    }

    private function setConversationStatus($chatId, $status)
    {
        $conversation = $this->getConversationStatus($chatId);
        $conversation->conversation_state = $status;
        $conversation->save();
    }

    public function setRegisterState($update)
    {
        $chatId = $update->getMessage()->getChat()->getId();
        $this->setConversationStatus($chatId, 'registro');

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => 'Por favor escribe la matrícula del estudiante',
        ]);
    }

    public function setDeleteState($update)
    {
        $chatId = $update->getMessage()->getChat()->getId();
        $this->setConversationStatus($chatId, 'borrar');

        Telegram::sendMessage([
            'chat_id' => $update->getMessage()->getChat()->getId(),
            'text' => 'Por favor escribe la matrícula del estudiante',
        ]);
    }
}
