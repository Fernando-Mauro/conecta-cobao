<?php

namespace App\Http\Controllers\v1\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Campus;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use App\Models\User;
use App\Traits\StudentTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class TutorsRegistrationController extends Controller
{

    use StudentTrait;

    public function createTutor($name, $phone, $campusId, $email, $password, $curp, $telegram)
    {
        try {
            $name = strtoupper(trim(strtr($name, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U'])));
            $phone = preg_replace('/\s+/', '', $phone);
            $email = preg_replace('/\s+/', '', $email);
            $password = trim($password);
            $curp = strtoupper(preg_replace('/\s+/', '', $curp));
            
            $user = User::where('email', $email)
                ->orWhere('name', $name)
                ->first();

            if (!$user) {

                $user = User::create([
                    'name' => ($name == "NULL") ? "Desconocido" : $name,
                    'email' => ($email == "NULL") ? "Desconocido" : $email,
                    'password' =>  Hash::make($password)
                ]);

                $role = Role::where('name', 'tutor')->first();
                $user->assignRole($role);
            }

            $tutor = Tutor::where('user_id', $user->id)->first();

            if (!$tutor) {

                $tutor = Tutor::create([
                    'phone' => ($phone == "NULL") ? "Desconocido" : $phone,
                    'campus_id' => $campusId,
                    'user_id' => $user->id,
                    'telegram_chat_id' => ($telegram != 0) ? $telegram : NULL
                ]);
            }

            $student = Student::where('curp', $curp)->first();

            if ($student) {
                $tutorStudent = TutorStudent::where('tutor_id', $tutor->id)
                    ->where('student_id', $student->id)
                    ->first();

                if (!$tutorStudent) {
                    TutorStudent::create([
                        'tutor_id' => $tutor->id,
                        'student_id' => $student->id
                    ]);
                }
            } else {
                throw new Exception('Estudiante no encontrado');
            }
        } catch (Exception $e) {
            if ($e->getMessage() === 'Estudiante no encontrado') {
                return Response::json([
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }
        }
    }

    public function registerTutor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string',
            'password' => 'required|string',
            'curp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json(["message" => 'Error en el formato de los datos'], 400);
        }

        try {
            $admin = Auth::user();
            $campusId = Admin::where('user_id', $admin->id)->first()->campus_id;

            $name = $request->input('name');
            $phone = $request->input('phone');
            $email = $request->input('email');
            $password = $request->input('password');
            $curp = $request->input('curp');
            
            $this->createTutor($name, $phone, $campusId, $email, $password, $curp, 0);

            return Response::json([
                'message' => 'Tutor registrado correctamente',
            ], 200);
        } catch (Exception $e) {
            Log::channel('daily')->error('Error al crear el tutor: ' . $e->getMessage());
            return Response::json([
                'message' => 'Error al crear el tutor: ' . $e->getMessage()
            ], 400);
        }
    }

    public function registerTutors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.nombre' => 'required|string',
            '*.telefono' => 'required|string',
            '*.email' => 'required|string',
            '*.contraseña' => 'required|string',
            '*.curp' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            return Response::json(["message" => 'Error en el formato de los datos'], 400);
        }

        try {

            $admin = Auth::user();
            $campusId = Admin::where('user_id', $admin->id)->first()->campus_id;
            
            foreach ($request->all() as $tutorRequest) {
                Log::channel('daily')->debug('intentando generar tutores');
                $name = $tutorRequest['nombre'];
                $phone = $tutorRequest['telefono'];
                $email = $tutorRequest['email'];
                $password = $tutorRequest['contraseña'];
                $curp = $tutorRequest['curp'];
                $telegram = $tutorRequest['telegram'];
                $this->createTutor($name, $phone, $campusId, $email, $password, $curp, $telegram);
            }

            return Response::json([
                'message' => 'Tutores registrados correctamente',
            ], 200);

        } catch (Exception $e) {
            Log::channel('daily')->error('Error al crear los tutores: ' . $e->getMessage());
            return Response::json([
                'message' => 'Error al crear los tutores: ' . $e->getMessage()
            ], 400);
        }
    }
}
