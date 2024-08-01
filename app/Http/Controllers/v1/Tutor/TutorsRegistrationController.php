<?php

namespace App\Http\Controllers\v1\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Campus;
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

    public function createTutor($name, $phone, $campusNumber, $email, $password, $curp)
    {
        try {

            $user = User::where('email', $email)
                ->orWhere('name', $name)
                ->first();

            if (!$user) {

                $user = User::create([
                    'name' => ($name == "NULL") ? "Desconocido" : $name,
                    'email' => ($email == "NULL") ? "Desconocido" : $email,
                    'password' => Hash::make($password)
                ]);

                $role = Role::where('name', 'tutor')->first();
                $user->assignRole($role);
            }

            $tutor = Tutor::where('user_id', $user->id)->first();

            if (!$tutor) {

                $tutor = Tutor::create([
                    'phone' => ($phone == "NULL") ? "Desconocido" : $phone,
                    'campus_id' => Campus::where('campus_number', $campusNumber)->first()->id,
                    'user_id' => $user->id
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
            $campusNumber = $request->input('campus');
            $email = $request->input('email');
            $password = $request->input('password');
            $curp = $request->input('curp');

            $this->createTutor($name, $phone, $campusNumber, $email, $password, $curp);

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
                $campusNumber = $tutorRequest['plantel'];
                $email = $tutorRequest['email'];
                $password = $tutorRequest['contraseña'];
                $curp = $tutorRequest['curp'];
                $this->createTutor($name, $phone, $campusNumber, $email, $password, $curp);
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
