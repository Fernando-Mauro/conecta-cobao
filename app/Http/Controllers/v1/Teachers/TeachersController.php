<?php

namespace App\Http\Controllers\v1\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TeachersController extends Controller
{
    //
    public function getTeachers()
    {
        $activeTeachers = Teacher::where('active', true)->select('id', 'name', 'phone', 'campus')->get();

        return Response::json($activeTeachers, 200);
    }

    public function getTeacherById($id)
    {
        $teacher = Teacher::where('id', $id)->select('name', 'phone', 'user_id')->first();

        if (!$teacher) {
            return Response::json(['message' => 'Docente no encontrado']);
        }
        $user = User::where('id', $teacher->user_id)->select('email')->first();

        if (!$user) {
            return Response::json(['message' => 'Usuario de docente no encontrado']);
        }

        return Response::json([
            'name' => $teacher->name,
            'phone' => $teacher->phone,
            'email' => $user->email
        ], 200);
    }


    public function editTeacherById($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages()->all();
            $messageStr = implode(" ", $messages);
            return Response::json(["message" => $messageStr], 400);
        }

        $teacher = Teacher::where('id', $id)->first();
        
        if (!$teacher) {
            return Response::json(['message' => 'Docente no encontrado']);
        }

        $teacher->update($request->only('name', 'phone'));
        $user = User::where('id', $teacher->user_id);
        
        if (!$user) {
            return Response::json(['message' => 'Usuario de docente no encontrado']);
        }

        $user->update($request->only('email'));

        return Response::json(["message" => 'Los datos se han actualizado correctamente'], 200);
    }

    public function deleteTeacherById($id)
    {
        $teacher = Teacher::where('id', $id)->first();
        if(!$teacher){
            return Response::json(['message' => 'Docente no encontrado'], 404);
        }
        $user = User::where('id', $teacher->user_id);
        
        if(!$user){
            return Response::json(['message' => 'Usuario del docente no encontrado'], 404);
        }
        $teacher->delete();
        $user->delete();
        
        return Response::json(['message' => 'Docente eliminado correctamente'], 200);
    }
}
