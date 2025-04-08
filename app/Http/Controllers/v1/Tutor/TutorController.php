<?php

namespace App\Http\Controllers\v1\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Campus;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TutorController extends Controller
{
    public function getTutorById($id)
    {
        $tutor = Tutor::where('id', $id)->select('phone', 'user_id', 'telegram_chat_id')->first();

        $tutorStudents = TutorStudent::where('tutor_id', $id)->select('student_id')->get();

        $students = Student::whereIn('id', $tutorStudents->pluck('student_id'))
                ->select('enrollment', 'id')
                ->get();
        
        if (!$tutor) {
            return Response::json(['message' => 'Tutor no encontrado']);
        }

        $user = User::where('id', $tutor->user_id)->select('email', 'name')->first();

        if (!$user) {
            return Response::json(['message' => 'Usuario no encontrado']);
        }

        return Response::json([
            'name' => $user->name,
            'phone' => $tutor->phone,
            'email' => $user->email,
            'telegram_chat_id' => $tutor->telegram_chat_id,
            'students' => $students,
        ], 200);
    }


    public function getTutorsByGroup($group)
    {
        $user = Auth::user();
        
        $admin = Admin::where('user_id', $user->id)->first();
        $group = Group::where('id', $group)->where('campus_id', $admin->campus_id)->first();

        $response = [];
        if($group){
            $students = Student::where('group_id', $group->id)->select('id')->get();
            $tutorIds = TutorStudent::whereIn('student_id', $students->pluck('id'))->select('tutor_id')->distinct()->get();
            $tutors = Tutor::whereIn('id', $tutorIds->pluck('tutor_id'))->select('id','phone', 'user_id')->get();
            
            // Get the names from de users
            foreach ($tutors as $tutor) {
                $response[] = [
                    'id' => $tutor->id,
                    'nombre' => User::where('id', $tutor->user_id)->select('name')->first()->name,
                    'telefono' => $tutor->phone,
                ];
            }

            $campus = Campus::where('id', $admin->campus_id)->select('campus_number')->first();
            
            foreach ($tutors as $tutor) {
                $tutor->campus = $campus->campus_number;
            }

        }else{
            return 'No hay grupo';
        }
    
        return Response::json($response);
    }
    

    public function editTutorById($id, Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string',
        ]);
        
        if($validator->fails()){
            return Response::json(['message' => 'Error en el formato de datos'], 400);
        }

        $tutor = Tutor::where('id', $id)->first();
        
        if (!$tutor) {
            return Response::json(['message' => 'Tutor no encontrado'], 404);
        }

        $tutor->update($request->only( 'phone'));
        
        $user = User::where('id', $tutor->user_id)->first();
        
        if (!$user) {
            return Response::json(['message' => 'Usuario no encontrado'], 404);
        } 

        $user->update($request->only('email', 'name'));
        return Response::json(['message' => 'Tutor actualizado correctamente'], 200);
    }
    public function resetPassword($id, Request $request){
        $tutor = Tutor::where('id', $id)->first();
        
        if (!$tutor) {
            return Response::json(['message' => 'Tutor no encontrado'], 404);
        }

        $user = User::where('id', $tutor->user_id)->first();
        
        if (!$user) {
            return Response::json(['message' => 'Usuario no encontrado'], 404);
        } 

        $user->update(['password' => Hash::make($request->input('password'))]);
        return Response::json(['message' => 'Contraseña actualizada correctamente'], 200);
    }
    public function resetTelegram($id, Request $request){
        $tutor = Tutor::where('id', $id)->first();
        
        if (!$tutor) {
            return Response::json(['message' => 'Tutor no encontrado'], 404);
        }

        $tutor->update(['telegram_chat_id' => null]);
        return Response::json(['message' => 'Telegram actualizado correctamente'], 200);
    }
}
