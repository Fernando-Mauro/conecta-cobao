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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class TutorsRegistrationController extends Controller
{

    use StudentTrait;

    public function createTutor($name, $phone, $campusId, $email, $password, $curp)
    {
        DB::beginTransaction();
        try {
            // Limpieza y normalización de los datos
            $name = strtoupper(trim(strtr($name, [
                'á' => 'a',
                'é' => 'e',
                'í' => 'i',
                'ó' => 'o',
                'ú' => 'u',
                'Á' => 'A',
                'É' => 'E',
                'Í' => 'I',
                'Ó' => 'O',
                'Ú' => 'U'
            ])));
            $phone = preg_replace('/\s+/', '', $phone);
            $email = preg_replace('/\s+/', '', $email);
            $password = trim($password);
            $curp = strtoupper(preg_replace('/\s+/', '', $curp));

            // Determinar el valor para email: si viene vacío, se usará el teléfono
            $lookupEmail = $email !== "" ? $email : $phone;

            // Verificar si ya existe un usuario con el email (o teléfono)
            $user = User::where('email', $lookupEmail)->first();
            if ($user) {
                // Si existe, revisamos que no se esté duplicando el tutor.
                // Verificamos también si el teléfono ya está registrado en algún tutor
                $existingTutor = Tutor::where('user_id', $user->id)
                    ->orWhere('phone', $phone)
                    ->first();

                if ($existingTutor) {
                    throw new Exception("Ya existe un tutor con este email o teléfono.");
                }
            }

            // Si el usuario no existe, se crea y se asigna el rol de tutor
            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $lookupEmail,
                    'password' => Hash::make($password)
                ]);
                $role = Role::where('name', 'tutor')->first();
                $user->assignRole($role, "api");
            }

            // Validar que no se intente crear el tutor duplicado (por teléfono o vinculación con el usuario)
            $tutor = Tutor::where('phone', $phone)
                ->orWhere('user_id', $user->id)
                ->first();

            if ($tutor) {
                throw new Exception("El tutor ya está registrado con este teléfono o usuario.");
            }

            // Crear el tutor
            $tutor = Tutor::create([
                'phone' => $phone,
                'campus_id' => $campusId,
                'user_id' => $user->id,
                'telegram_chat_id' => null
            ]);

            // Vincular el tutor con el estudiante, si existe
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
                throw new Exception('Estudiante no encontrado.');
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function registerTutor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
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
            try {
                $this->createTutor($name, $phone, $campusId, $email, $password, $curp);
            } catch (Exception $e) {
                return Response::json([
                    'message' => 'Error al crear el tutor: ' . $e->getMessage()
                ], 400);
            }

            return Response::json([
                'message' => 'Tutor registrado correctamente',
            ], 200);
        } catch (Exception $e) {
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
            '*.curp' => 'required|string'
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

                $this->createTutor($name, $phone, $campusId, $email, $password, $curp, 0);
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
