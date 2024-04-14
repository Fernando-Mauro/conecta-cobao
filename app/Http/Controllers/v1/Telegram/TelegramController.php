<?php

namespace App\Http\Controllers\v1\Telegram;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ConversationStatus;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use JWTAuth;

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
            Log::channel('daily')->debug('Mensaje raro');
        } else {
            if ($update->isType('callback_query')) {
                $callbackData = $update->getCallbackQuery()->getData();

                if ($callbackData === 'registrar') {
                    $this->setRegisterState($update);
                } else if ($callbackData === 'borrar') {
                    $this->setDeleteState($update);
                }
            } else {
                Log::channel('daily')->debug('Mensaje normal');
                $chatId = $update->getMessage()->getChat()->getId();

                // Busca el estado actual en la base de datos
                $conversation = ConversationStatus::where('chat_id', $chatId)->first();

                if ($conversation) {
                    $currentState = $conversation->conversation_state;
                    switch ($currentState) {
                        case 'registro':
                            $enrollment = $update->getMessage()->getText();
                            $this->setEnrollmentToConversationStatus($enrollment, $chatId);
                            break;
                        case 'password':
                            $password = $update->getMessage()->getText();
                            $this->handleRegistration($chatId, $password);
                            break;
                        case 'borrar':
                            $enrollment = $update->getMessage()->getText();
                            $this->handleDeletion($enrollment, $chatId);
                            break;
                        default:
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
            $conversation->conversation_state = null;
            $conversation->enrollment = null;
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

    public function handleRegistration($chatId, $password)
    {
        Log::channel('daily')->info('Inicio del registro');

        $conversation = $this->getConversationStatus($chatId);
        Log::channel('daily')->info('Estado de la conversación obtenido: ' . json_encode($conversation));

        $student = Student::where('enrollment', $conversation->enrollment)->first();
        Log::channel('daily')->info('Estudiante obtenido: ' . json_encode($student));

        if ($student) {
            $tutorStudent = TutorStudent::where('student_id', $student->id)->first();
            Log::channel('daily')->info('Tutor del estudiante obtenido: ' . json_encode($tutorStudent));

            if ($tutorStudent) {
                $tutor = Tutor::where('id', $tutorStudent->tutor_id)->first();
                Log::channel('daily')->info('Tutor obtenido: ' . json_encode($tutor));

                if ($tutor) {
                    $userTutor = User::where('id', $tutor->user_id)->first();
                    Log::channel('daily')->info('Usuario del tutor obtenido: ' . json_encode($userTutor));

                    if (JWTAuth::attempt(['email' => $userTutor->email, 'password' => $password]) && $tutor->telegram_chat_id == null) {
                        $tutor->telegram_chat_id = $chatId;
                        $tutor->save();
                        $message = 'Número telefónico asignado correctamente.' . "\n" . 'A partir de este momento recibirás notificaciones sobre la entrada y salida del estudiante.';
                        $this->setConversationStatus($chatId, null);
                        Log::channel('daily')->info('Autenticación exitosa y chatId asignado al tutor');
                    } else {
                        $message = 'El codigo de seguridad es incorrecto o ya hay otro número registrado, por favor intenta de nuevo';
                        Log::channel('daily')->info('Falló la autenticación');
                    }
                } else {
                    $message = 'No se pudo encontrar un tutor activo para este estudiante.';
                    Log::channel('daily')->info('No se encontró un tutor activo');
                }
            } else {
                $message = 'No se pudo encontrar una relación de tutor para este estudiante.';
                Log::channel('daily')->info('No se encontró una relación de tutor');
            }
        } else {
            $message = 'No se encontró ningún estudiante con la matrícula proporcionada.';
            Log::channel('daily')->info('No se encontró un estudiante');
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message
        ]);

        Log::channel('daily')->info('Mensaje enviado: ' . $message);
    }


    public function setEnrollmentToConversationStatus($enrollment, $chatId)
    {
        if ($this->isValidEnrollment($enrollment)) {
            $conversation = $this->getConversationStatus($chatId);
            $conversation->enrollment = $enrollment;
            $conversation->save();
            $this->setConversationStatus($chatId, 'password');
            $message = 'Estamos a punto de terminar! Por favor escriba su código de seguridad';
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
