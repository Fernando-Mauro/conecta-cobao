<?php

namespace App\Http\Controllers\v1\Telegram;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ConversationStatus;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function isValidEnrollment($enrollment)
    {
        // Validación: Verificar si la matrícula coincide con la expresión regular
        if (!preg_match('/^(?:[0-9]{2}[abAB][0-9]{7,8}|[sS][aA][0-9]{7})$/', $enrollment)) {
            return false;
        }
        return true;
    }

    public function inbound(Request $request)
    {
        $update = Telegram::commandsHandler(true);
        
        if ($update->isType('my_chat_member')) {
            Log::channel('daily')->debug('Mensaje rarito');
        } else {
            if ($update->isType('callback_query')) {
                $callbackData = $update->getCallbackQuery()->getData();

                if ($callbackData === 'registrar') {
                    $this->setRegisterState($update);
                } else if ($callbackData === 'borrar') {
                    $this->setDeleteState($update);
                }
            } else {
                Log::channel('daily')->debug('Mensaje normalito');
                $chatId = $update->getMessage()->getChat()->getId();

                // Busca el estado actual en la base de datos
                $conversation = ConversationStatus::where('chat_id', $chatId)->first();

                if ($conversation) {
                    $currentState = $conversation->conversation_state;

                    // Aplica lógica según el estado actual
                    if ($currentState === 'registro') {
                        // Lógica para procesar un mensaje en estado "registro"
                        $enrollment = $update->getMessage()->getText();
                        $this->handleRegistration($enrollment, $chatId);
                    } elseif ($currentState === 'borrar') {
                        // Lógica para procesar un mensaje en estado "borrar"
                        $enrollment = $update->getMessage()->getText();
                        $this->handleDeletion($enrollment, $chatId);
                    } else {
                        Telegram::sendMessage([
                            'chat_id' => $chatId,
                            'text' => 'Lo siento, prueba con otra palabra o comando'
                        ]);
                    }
                }
            }
        }

        return 'ok';
    }

    public function getConversationStatus($chatId)
    {
        $conversation = ConversationStatus::where('chat_id', $chatId)->first();

        if (!$conversation) {
            // Si no se encuentra un registro, crea uno con un estado inicial (por ejemplo, "none")
            $conversation = new ConversationStatus();
            $conversation->chat_id = $chatId;
            $conversation->conversation_state = null; // Estado inicial
            $conversation->save();
        }

        return $conversation;
    }

    public function setConversationStatus($chatId, $status)
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

    public function handleRegistration($enrollment, $chatId)
    {
        if ($this->isValidEnrollment($enrollment)) {
            $student = Student::where('enrollment', $enrollment)->first();

            if ($student) {
                $tutorStudent = TutorStudent::where('student_id', $student->id)->first();

                if ($tutorStudent) {
                    $tutor = Tutor::find($tutorStudent->tutor_id);

                    if ($tutor) {
                        $tutor->telegram_chat_id = $chatId;
                        $tutor->save();

                        $message = 'Número telefónico asignado correctamente.' . "\n" . 'A partir de este momento recibirás notificaciones sobre la entrada y salida del estudiante.';
                        $this->setConversationStatus($chatId, null);
                    } else {
                        $message = 'No se pudo encontrar un tutor activo para este estudiante.';
                    }
                } else {
                    $message = 'No se pudo encontrar una relación de tutor para este estudiante.';
                }
            } else {
                $message = 'No se encontró ningún estudiante con la matrícula proporcionada.';
            }
        } else {
            $message = 'La matrícula proporcionada no es válida 😠' . "\n" . 'Ingresa de nuevo la matrícula.';
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message
        ]);
    }


    public function handleDeletion($enrollment, $chatId)
    {
        // Busca al estudiante por la matrícula proporcionada
        $student = Student::where('enrollment', $enrollment)->first();
        if ($this->isValidEnrollment($enrollment)) {
            if ($student) {
                // Encuentra la relación del tutor con el estudiante
                $tutorStudent = TutorStudent::where('student_id', $student->id)->first();

                if ($tutorStudent) {
                    // Encuentra al tutor correspondiente
                    $tutor = Tutor::find($tutorStudent->tutor_id);

                    if ($tutor) {
                        // Establece telegram_chat_id en null
                        $tutor->telegram_chat_id = null;
                        $tutor->save();
                        $message = 'Se ha eliminado la asociación del tutor con el estudiante. Ya no recibirás notificaciones.';
                        $this->setConversationStatus($chatId, null);
                    } else {
                        $message = 'No se pudo encontrar al tutor asociado a este estudiante.';
                    }
                } else {
                    $message = 'No se encontró una relación de tutor para este estudiante.';
                }
            } else {
                $message = 'No se encontró ningún estudiante con la matrícula proporcionada.';
            }
        } else {
            $message = 'La matrícula proporcionada no es válida 😠' . "\n" . 'Ingresa de nuevo la matrícula.';
        }


        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message
        ]);
    }
}
