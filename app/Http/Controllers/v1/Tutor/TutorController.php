<?php

namespace App\Http\Controllers\v1\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TutorController extends Controller
{
    public function getTutorById($id)
    {
        $tutor = Tutor::where('id', $id)->select('name', 'phone', 'user_id')->first();
        if (!$tutor) {
            return Response::json(['message' => 'Tutor no encontrado']);
        }
        $user = User::where('id', $tutor->user_id)->select('email')->first();

        if (!$user) {
            return Response::json(['message' => 'Usuario no encontrado']);
        }

        return Response::json([
            'name' => $tutor->name,
            'phone' => $tutor->phone,
            'email' => $user->email,
        ], 200);
    }


    public function getTutorsByGroup($group)
    {
        $students = Student::where('group', $group)->select('id')->get();
        $tutorIds = TutorStudent::whereIn('student_id', $students->pluck('id'))->select('tutor_id')->distinct()->get();
        $tutors = Tutor::whereIn('id', $tutorIds->pluck('tutor_id'))->select('id', 'name', 'phone', 'campus')->get();

        return Response::json($tutors);
    }

    public function editTutorById($id, Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string'
        ]);
        
        if($validator->fails()){
            return Response::json(['message' => 'Error en el formato de datos'], 400);
        }

        $tutor = Tutor::where('id', $id)->first();
        
        if (!$tutor) {
            return Response::json(['message' => 'Tutor no encontrado'], 404);
        }

        $tutor->update($request->only('name', 'phone'));
        
        $user = User::where('id', $tutor->user_id)->first();
        
        if (!$user) {
            return Response::json(['message' => 'Usuario no encontrado'], 404);
        } 

        $user->update($request->only('email', 'name'));
        return Response::json(['message' => 'Tutor actualizado correctamente'], 200);
    }
}
