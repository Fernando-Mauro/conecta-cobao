<?php

namespace App\Http\Controllers\v1\Student;

use App\Http\Controllers\Controller;
use App\Jobs\SendMessage;
use App\Models\Student;
use App\Models\StudentCheckIn;
use App\Models\StudentCheckOut;
use App\Models\Tutor;
use App\Models\TutorStudent;
use App\Models\User;
use App\Traits\StudentTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Telegram\Bot\Laravel\Facades\Telegram;
use GuzzleHttp\Client;

class StudentCheckController extends Controller
{
    use StudentTrait;

    public function determineCheckType($student_id)
    {
        $date = date('Y-m-d');
        $checkIns = StudentCheckIn::where('student_id', $student_id)->whereDate('created_at', $date)->count();
        $checkOuts = StudentCheckOut::where('student_id', $student_id)->whereDate('created_at', $date)->count();

        if ($checkIns > $checkOuts) {
            return 'out';
        } else {
            return 'in';
        }
    }


    public function registerStudentCheck(Request $request, $identifier)
    {
        try {    

            if ($this->isValidEnrollment($identifier)) {
                $student = Student::where('enrollment', $identifier)->first();
            } elseif ($this->isValidCurp($identifier)) {
                $student = Student::where('curp', $identifier)->first();
            }
    
            if (!$student) {
                return Response::json(['message' => 'Estudiante no encontrado'], 404);
            }
    
            $type = $this->determineCheckType($student->id);
            
            if ($type === 'in') {
                $this->registerIn($student);
                return Response::json(["message" => "Registro y mensaje enviado correctamente"], 200);
            } elseif ($type === 'out') {
                $this->registerOut($student);
                return Response::json(["message" => "Registro y mensaje enviado correctamente"], 200);
            } else {
                return Response::json(["message" => "Tipo de registro inválido"], 400);
            }
        } catch (Exception $err) {
            return Response::json(['message' => 'Ha ocurrido un error ' . $err], 500);
        }
    }
    


    public function registerIn($student)
    {
        $checkIn = new StudentCheckIn();
        $checkIn->student_id = $student->id;
        $checkIn->save();
        $message = 'entrada';
        return $this->notifyTutor($student, $message);
    }

    public function registerOut($student)
    {
        $checkOut = new StudentCheckOut();
        $checkOut->student_id = $student->id;
        $checkOut->save();
        $message = 'salida';
        return $this->notifyTutor($student, $message);
    }

    public function notifyTutor($student, $message)
    {
        $tutorStudent = TutorStudent::where('student_id', $student->id)->first();
        
        if ($tutorStudent) {
            $tutor = Tutor::find($tutorStudent->tutor_id);
            if ($tutor) {
                $time = date('H:i');
                $telegram_chat_id = $tutor->telegram_chat_id;
                if ($telegram_chat_id) {
                    SendMessage::dispatch($student, $message, $time)->onQueue('messages');
                }
            }
        }
    }
}
