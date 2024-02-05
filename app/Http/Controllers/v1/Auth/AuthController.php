<?php

namespace App\Http\Controllers\v1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\Admin;
use App\Models\Tutor;
use App\Models\Teacher;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);
        $roleName = $request->input('role');
        
        if ($roleName) {
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $user->assignRole($role);
            }
        }

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return Response::json(['error' => 'Credenciales invalidas'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        $user = Auth::user();

        $role = $user->roles;

        $expirationTimeInMinutes = 30 * 24 * 60;
        $cookie = cookie('jwt', $token, $expirationTimeInMinutes);
        return response()
            ->json(['message' => 'success', 'role' => $role[0]->name])
            ->withCookie($cookie);
    }



    public function getAuthenticatedUser()
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
                $roles = $user->getRoleNames(); // Esta línea obtiene los nombres de los roles del usuario

                $data = [
                    'name' => $user->name,
                    'email' => $user->email,
                ];

                foreach ($roles as $role) {
                    if ($role === 'admin' || $role === 'teacher' || $role === 'tutor') {
                        $additionalInfo = $this->getAdditionalInfo($user->id, $role);
                        $data['phone'] = $additionalInfo->phone;
                    }
                }

                return Response::json($data, 200);
            } else {
                return Response::json(['message' => 'No se pudo autenticar al usuario'], 401);
            }
        } catch (\Exception $e) {
            // manejo de errores
            return Response::json(['message' => 'Ocurrió un error al obtener los datos del usuario'], 500);
        }
    }


    private function getAdditionalInfo($userId, $role)
    {
        try {
            switch ($role) {
                case 'admin':
                    $admin = Admin::where('user_id', $userId)->select('phone')->first();
                    return $admin;
                case 'teacher':
                    $teacher = Teacher::where('user_id', $userId)->select('phone')->first();
                    return $teacher;
                case 'tutor':
                    $tutor = Tutor::where('user_id', $userId)->select('phone')->first();
                    return $tutor;
                default:
                    return [];
            }
        } catch (\Exception $e) {
            // manejo de errores
            return ['message' => 'Ocurrió un error al obtener la información adicional del usuario'];
        }
    }

    public function logout(Request $request)
    {
        $cookie = Cookie::forget('jwt');
        return response([
            'message' => 'sucess'
        ])->withCookie($cookie);
    }
}
