<?php

namespace App\Http\Controllers\v1\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use App\Models\User;
use App\Traits\StudentTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class TutorsRegistrationController extends Controller
{

    use StudentTrait;

    public function createTutor($name, $phone, $campus, $email, $password, $curp)
    {
        try {
            Log::channel('daily')->debug('intentando buscar el user');
            // Verificar si el usuario ya existe
            $user = User::where('email', $email)
                ->orWhere('name', $name)
                ->first();
            Log::channel('daily')->debug('Agarrando user');

            if (!$user) {
                Log::channel('daily')->debug('user no encontrado');
                $user = User::create([
                    'name' => ($name == "NULL") ? "Desconocido" : $name,
                    'email' => ($email == "NULL") ? "Desconocido" : $email,
                    'password' => Hash::make($password)
                ]);
                Log::channel('daily')->debug('user creado');
                $role = Role::where('name', 'tutor')->first();
                $user->assignRole($role);
            }

            // Verificar si el tutor ya existe
            $tutor = Tutor::where('user_id', $user->id)->first();
            Log::channel('daily')->debug('buscando tutor');
            if (!$tutor) {
                Log::channel('daily')->debug('tutor no encontrado');
                $tutor = Tutor::create([
                    'name' => ($name == "NULL") ? "Desconocido" : $name,
                    'phone' => ($phone == "NULL") ? "Desconocido" : $phone,
                    'campus' => $campus,
                    'user_id' => $user->id
                ]);
                Log::channel('daily')->debug('tutor creado');
            }

            $student = Student::where('curp', $curp)->first();

            if ($student) {
                // Verificar si la relación tutor-estudiante ya existe
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
            } else {
                return Response::json([
                    'message' => 'Error al enviar el tutor'
                ], 400);
            }
        }
    }

    public function registerTutor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'campus' => 'required|integer',
            'email' => 'required|string',
            'password' => 'required|string',
            'curp' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidCurp($value)) {
                        $fail($attribute . ' es inválido.');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            return Response::json(["message" => 'Error en el formato de los datos'], 400);
        }

        try {
            $name = $request->input('name');
            $phone = $request->input('phone');
            $campus = $request->input('campus');
            $email = $request->input('email');
            $password = $request->input('password');
            $curp = $request->input('curp');

            $this->createTutor($name, $phone, $campus, $email, $password, $curp);

            return Response::json([
                'message' => 'Tutor registrado correctamente',
            ], 200);
        } catch (Exception $e) {
            return Response::json([
                'message' => 'Error al enviar el Tutor'
            ], 400);
        }
    }

    public function registerTutors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.nombre' => 'required|string',
            '*.telefono' => 'required|string',
            '*.plantel' => 'required|integer',
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

            foreach ($request->all() as $tutorRequest) {
                Log::channel('daily')->debug('intentando generar tutores');
                $name = $tutorRequest['nombre'];
                $phone = $tutorRequest['telefono'];
                $campus = $tutorRequest['plantel'];
                $email = $tutorRequest['email'];
                $password = $tutorRequest['contraseña'];
                $curp = $tutorRequest['curp'];
                $this->createTutor($name, $phone, $campus, $email, $password, $curp);
            }

            return Response::json([
                'message' => 'Tutores registrados correctamente',
            ], 200);
        } catch (Exception $e) {
            return Response::json([
                'message' => 'Error al enviar los tutores'
            ], 400);
        }
    }
}
