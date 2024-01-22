<?php

namespace App\Http\Controllers\v1\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class TeachersRegistrationController extends Controller
{
    public function createTeacher($name, $phone, $campus, $email, $password)
    {
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password)
            ]);
            $role = Role::where('name', 'teacher')->first();
            $user->assignRole($role);

            Teacher::create([
                'name' => $user->name,
                'phone' => $phone,
                'campus' => $campus,
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'message' => 'Error al enviar el docente'
            ], 400);
        }
    }

    public function registerTeacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'campus' => 'required|integer',
            'email' => 'required|string',
            'password' => 'required|string'
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

            $this->createTeacher($name, $phone, $campus, $email, $password);

            return Response::json([
                'message' => 'Docente registrado correctamente',
            ], 200);

        } catch (\Exception $e) {
            return Response::json([
                'message' => 'Error al enviar el docente'
            ], 400);
        }
    }

    public function registerTeachers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.nombre' => 'required|string',
            '*.telefono' => 'required|string',
            '*.plantel' => 'required|integer',
            '*.email' => 'required|string',
            '*.contraseña' => 'required|string'
        ]);

        if ($validator->fails()) {
            return Response::json(["message" => 'Error en el formato de los datos'], 400);
        }

        try {

            foreach ($request->all() as $teachersRequest) {
                $name = $teachersRequest['nombre'];
                $phone = $teachersRequest['telefono'];
                $campus = $teachersRequest['plantel'];
                $email = $teachersRequest['email'];
                $password = $teachersRequest['contraseña'];
                $this->createTeacher($name, $phone, $campus, $email, $password);
            }

            return Response::json([
                'message' => 'Docentes registrados correctamente',
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'message' => 'Error al enviar los docentes'
            ], 400);
        }
    }
}
