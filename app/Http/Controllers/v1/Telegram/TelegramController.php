<?php

namespace App\Http\Controllers\v1\Telegram;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ConversationStatus;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Tutor;
use App\Models\TutorStudent;
use App\Models\User;
use App\Traits\StudentTrait;
use App\Traits\TeacherTrait;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use JWTAuth;

class TelegramController extends Controller
{

    use StudentTrait;
    use TeacherTrait;

    public function inbound(Request $request)
    {
        $update = Telegram::getWebhookUpdates();
        $chatId = $update->getMessage()->getChat()->getId();

        // Busca el estado actual en la base de datos
        $conversation = ConversationStatus::where('chat_id', $chatId)->first();

        if (!$conversation || $update->hasCommand()) {
            Telegram::commandsHandler(true);
            return 'ok';
        } else {
            $currentState = $conversation->conversation_state;
            $type_user = $conversation->type_user;
            $this->handleConversationStatus($update, $currentState, $type_user);
        }

        return 'ok';
    }

    private function handleConversationStatus($update, $currentState, $type_user)
    {
        $chatId = $update->getMessage()->getChat()->getId();

        switch ($currentState) {
            case 'registro':
                $identifier = $update->getMessage()->getText();
                $this->setIdentifierToConversationStatus($identifier, $chatId, $type_user);
                break;
            case 'password':
                $password = $update->getMessage()->getText();
                $this->handleRegistration($chatId, $password, $type_user);
                break;
            case 'borrar':
                $enrollment = $update->getMessage()->getText();
                $this->handleDeletion($enrollment, $chatId);
                break;
        }
    }
    public function setIdentifierToConversationStatus($identifier, $chatId, $type_user)
    {
        if ($type_user == 'teacher' && !$this->isValidEmail($identifier)) {
            $message = 'El correo electrónico proporcionado no es válido 😅' . "\n" . 'Ingresa de nuevo el correo electrónico.';
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $message
            ]);
            return;
        }

        if ($type_user == 'tutor' && !$this->isValidEnrollment($identifier)) {
            $message = 'La matrícula proporcionada no es válida 😅' . "\n" . 'Ingresa de nuevo la matrícula.';

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $message
            ]);

            return;
        }

        $conversation = $this->getConversationStatus($chatId);
        $conversation->identifier = $identifier;
        $conversation->save();

        $this->setConversationStatus($chatId, 'password');
        $message = 'Estamos a punto de terminar! Por favor escriba su contraseña';

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message
        ]);
    }
    public function getConversationStatus($chatId)
    {
        $conversation = ConversationStatus::where('chat_id', $chatId)->first();
        return $conversation;
    }

    public function setConversationStatus($chatId, $status)
    {
        $conversation = $this->getConversationStatus($chatId);
        $conversation->conversation_state = $status;
        $conversation->save();
    }

    public function handleRegistration($chatId, $password, $type_user)
    {
        $conversation = $this->getConversationStatus($chatId);

        if ($type_user == 'teacher')
            $this->handleTeacherRegistration($conversation, $chatId, $password);

        if ($type_user == 'tutor')
            $this->handleTutorRegistration($conversation, $chatId, $password);
    }

    private function handleTutorRegistration($conversation, $chatId, $password)
    {
        // TODO: Revisar si al finalizar el registro hay que borrar el conversation status

        $student = Student::where('enrollment', $conversation->identifier)->first();
        if (!$student) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'No se encontró ningún estudiante con la matrícula proporcionada.'
            ]);
            return;
        }

        $tutorStudent = TutorStudent::where('student_id', $student->id)->first();
        if (!$tutorStudent) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'No se encontró una relación de tutor para este estudiante.'
            ]);
            return;
        }

        $tutor = Tutor::where('id', $tutorStudent->tutor_id)->first();
        if (!$tutor) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'No se encontró un tutor activo para este estudiante.'
            ]);
            return;
        }

        $userTutor = User::where('id', $tutor->user_id)->first();
        if (JWTAuth::attempt(['email' => $userTutor->email, 'password' => $password]) && $tutor->telegram_chat_id == null) {
            $tutor->telegram_chat_id = $chatId;
            $tutor->save();
            $message = 'Número telefónico asignado correctamente.' . "\n" . 'A partir de este momento recibirás notificaciones sobre la entrada y salida del estudiante.';


            $this->setConversationStatus($chatId, null);
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $message
            ]);
            return;
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => 'El codigo de seguridad es incorrecto o ya hay otro número registrado, por favor intenta de nuevo'
        ]);
        return;
    }

    private function handleTeacherRegistration($conversation, $chatId, $password)
    {
        $teacher = Teacher::where('email', $conversation->identifier)->first();
        if (!$teacher) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'No se encontró ningún docente con el correo proporcionado.'
            ]);
            return;
        }

        $userTeacher = User::where('id', $teacher->user_id)->first();
        if (JWTAuth::attempt(['email' => $userTeacher->email, 'password' => $password]) && $teacher->telegram_chat_id == null) {
            $teacher->telegram_chat_id = $chatId;
            $teacher->save();
            $message = 'Número telefónico asignado correctamente.' . "\n" . 'A partir de este momento recibirás notificaciones sobre la entrada y salida del estudiante.';

            $this->setConversationStatus($chatId, null);
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $message
            ]);
            return;
        }

        if($teacher->telegram_chat_id != null){
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Ya hay un número registrado con este correo electrónico.'
            ]);
            return;
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => 'El codigo de seguridad es incorrecto, por favor intenta de nuevo'
        ]);

    }
    // public function handleDeletion($enrollment, $chatId)
    // {
    //     // Busca al estudiante por la matrícula proporcionada
    //     $student = Student::where('enrollment', $enrollment)->first();
    //     if ($this->isValidEnrollment($enrollment)) {
    //         if ($student) {
    //             // Encuentra la relación del tutor con el estudiante
    //             $tutorStudent = TutorStudent::where('student_id', $student->id)->first();

    //             if ($tutorStudent) {
    //                 // Encuentra al tutor correspondiente
    //                 $tutor = Tutor::find($tutorStudent->tutor_id);

    //                 if ($tutor) {
    //                     // Establece telegram_chat_id en null
    //                     $tutor->telegram_chat_id = null;
    //                     $tutor->save();
    //                     $message = 'Se ha eliminado la asociación del tutor con el estudiante. Ya no recibirás notificaciones.';
    //                     $this->setConversationStatus($chatId, null);
    //                 } else {
    //                     $message = 'No se pudo encontrar al tutor asociado a este estudiante.';
    //                 }
    //             } else {
    //                 $message = 'No se encontró una relación de tutor para este estudiante.';
    //             }
    //         } else {
    //             $message = 'No se encontró ningún estudiante con la matrícula proporcionada.';
    //         }
    //     } else {
    //         $message = 'La matrícula proporcionada no es válida 😠' . "\n" . 'Ingresa de nuevo la matrícula.';
    //     }


    //     Telegram::sendMessage([
    //         'chat_id' => $chatId,
    //         'text' => $message
    //     ]);
    // }
}
