<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AdminRegistrationController extends Controller
{
    public function createAdmin($name, $phone, $campusId, $email, $password)
    {
        try {
            Log::channel('daily')->info('El id del campus es ' . $campusId);
    
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password)
            ]);
    
            $role = Role::where('name', 'admin')->first();
            $user->assignRole($role);
    
            Admin::create([
                'phone' => $phone,
                'campus_id' => $campusId,
                'user_id' => $user->id
            ]);
    
            Log::channel('daily')->info('El admin se creó');
        } catch (\Exception $e) {
            // Registramos el error antes de propagarlo
            Log::channel('daily')->error('Error al crear el admin: ' . $e->getMessage());
            // Lanza la excepción para que se maneje en el método que llama a este
            throw $e;
        }
    }
    
    public function registerAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'campus' => 'required|integer',
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ]);
    
        if ($validator->fails()) {
            return Response::json(["message" => 'Error en el formato de los datos'], 400);
        }
    
        DB::beginTransaction();
    
        try {
            $userId = Auth::id();
            $campusId = Admin::where('user_id', $userId)->first()->campus_id;
    
            $name = $request->input('name');
            $phone = $request->input('phone');
            $email = $request->input('email');
            $password = $request->input('password');
    
            $this->createAdmin($name, $phone, $campusId, $email, $password);
            DB::commit();
    
            return Response::json([
                'message' => 'Administrador registrado correctamente',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('daily')->error('Error al registrar el admin: ' . $e->getMessage());
            return Response::json([
                'message' => 'Error al enviar el administrador'
            ], 400);
        }
    }
    

    public function registerAdmins(Request $request)
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
            $userId = Auth::id();
            $campusId = Admin::where('user_id', $userId)->first()->campus_id;

            foreach ($request->all() as $adminsRequest) {
                $name = $adminsRequest['nombre'];
                $phone = $adminsRequest['telefono'];
                $email = $adminsRequest['email'];
                $password = $adminsRequest['contraseña'];
                $this->createAdmin($name, $phone, $campusId, $email, $password);
            }


            return Response::json([
                'message' => 'Administradores registrados correctamente',
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'message' => 'Error al enviar los administradores'
            ], 400);
        }
    }
}
