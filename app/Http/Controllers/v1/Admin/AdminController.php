<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Groups;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    //
    public function getAllUsers()
    {
        $teachersCount = Teacher::count();
        $studentsCount = Student::count();
        $adminsCount = Admin::count();
        $tutorsCount = Tutor::count();

        return response()->json([
            'teachers' => $teachersCount,
            'students' => $studentsCount,
            'admins' => $adminsCount,
            'tutors' => $tutorsCount,
        ]);
    }
    public function getAllAdmins()
    {
        $admins = Admin::where('active', true)
            ->with('campus:id,campus_number,name')  // Carga la relación 'campus'
            ->select('id', 'name', 'email', 'campus_id')
            ->get()
            ->map(function ($admin) {  // Transforma cada admin
                return [
                    'id' => $admin->id,
                    'nombre' => $admin->name,
                    'correo' => $admin->email,
                    'nombre de plantel' => $admin->campus->name,
                    'número de plantel' => $admin->campus->campus_number,
                ];
            });

        return Response::json($admins, 200);
    }

    public function getAdminById($id)
    {
        $admin = Admin::where('id', $id)->first();
        if (!$admin) {
            return Response::json(['message' => 'Administrador no encontrado'], 200);
        }
        return Response::json($admin, 200);
    }

    public function editAdminById($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string'
        ]);

        if ($validator->fails()) {
            return Response::json(['message' => 'Error en el formato de datos'], 400);
        }

        $admin = Admin::where('id', $id)->first();

        if (!$admin) {
            return Response::json(['message' => 'Admin no encontrado'], 404);
        }

        $admin->update($request->only('name', 'phone'));

        $user = User::where('id', $admin->user_id)->first();

        if (!$user) {
            return Response::json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->update($request->only('email', 'name'));
        return Response::json(['message' => 'Admin actualizado correctamente'], 200);
    }

    public function deleteAdminById($id)
    {
        $admin = Admin::where('id', $id)->first();

        if (!$admin) {
            return Response::json(['message' => 'Admin no encontrado'], 404);
        }

        $admin->delete();
        return Response::json(['message' => 'Admin eliminado correctamente'], 200);
    }
    public function getGroups($semester)
    {
        $userId = Auth::id();

        if (!$userId) {
            return Response::json(['message' => 'No se encuentra autenticado']);
        }

        $admin = Admin::where('user_id', $userId)->first();
        $teacher = Teacher::where('user_id', $userId)->first();

        if ($admin) {
            $groups = Groups::where('campus_id', $admin->campus_id)->get();
        } elseif ($teacher) {
            $groups = Groups::where('campus_id', $teacher->campus_id)->get();
        } else {
            return Response::json(['message' => 'El usuario no es ni administrador ni profesor']);
        }

        $groupsFilter = [];
        foreach ($groups as $group) {
            // Verificar si el nombre del grupo comienza con el semestre
            if (strpos($group->name, $semester) === 0)
                array_push($groupsFilter, $group);
        }

        return Response::json($groupsFilter, 200);
    }
}
