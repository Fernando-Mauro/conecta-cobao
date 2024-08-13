<?php

namespace App\Http\Controllers\v1\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Campus;
use App\Models\Group;
use App\Models\GroupSubjectTeacher;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TeachersController extends Controller
{
    //
    public function getTeachers()
    {
        $user = Auth::user();

        $admin = Admin::where('user_id', $user->id)->first();
        $campus = Campus::where('id', $admin->campus_id)->first();

        if (!$campus) {
            return Response::json(['message' => 'Campus no encontrado'], 404);
        }

        $activeTeachers = Teacher::where('campus_id', $admin->campus_id)
            ->with('user:id,name,email')
            ->select('id', 'phone', 'user_id')    
            ->get();


        $activeTeachers->transform(function ($teacher) use ($campus) {
            return [
                'id' => $teacher->id,
                'nombre' => $teacher->user->name,
                'telefono' => $teacher->phone,
                'plantel' => $campus->campus_number,
            ];
        });

        return Response::json($activeTeachers, 200);
    }


    public function getTeacherById($id)
    {
        $teacher = Teacher::where('id', $id)
            ->with('user')
            ->select('phone', 'user_id')->first();

        if (!$teacher) {
            return Response::json(['message' => 'Docente no encontrado']);
        }

        return Response::json([
            'name' => $teacher->user->name,
            'phone' => $teacher->phone,
            'email' => $teacher->user->email
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

        $teacher->update($request->only('name'));
       
        $user = User::where('id', $teacher->user_id);

        if (!$user) {
            return Response::json(['message' => 'Usuario de docente no encontrado']);
        }

        $user->update($request->only('name','email'));

        return Response::json(["message" => 'Los datos se han actualizado correctamente'], 200);
    }

    public function deleteTeacherById($id)
    {
        $teacher = Teacher::where('id', $id)->first();
        if (!$teacher) {
            return Response::json(['message' => 'Docente no encontrado'], 404);
        }
        $user = User::where('id', $teacher->user_id);

        if (!$user) {
            return Response::json(['message' => 'Usuario del docente no encontrado'], 404);
        }
       
        $teacher->delete();
       
        $user->delete();

        return Response::json(['message' => 'Docente eliminado correctamente'], 200);
    }

    public function assignSubject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'groups' => 'required|array',
        ]); 

        if ($validator->fails()) {
            $messages = $validator->messages()->all();
            $messageStr = implode(" ", $messages);
            return Response::json(["message" => $messageStr], 400);
        }

        $teacher = Teacher::find($request->teacher_id);

        if (!$teacher) {
            return Response::json(['message' => 'Docente no encontrado'], 404);
        }

        $subject = Subject::find($request->subject_id);
        
        if(!$subject){
            return Response::json(['message' => 'Materia no encontrada'], 404);
        }

        try{

            DB::beginTransaction();
            
            foreach ($request->groups as $groupId) {
    
                $group = Group::find($groupId);
                
                if(!$group)
                    throw new \Exception('Grupo no encontrado');
                
                
                $groupSubjectTeacher = GroupSubjectTeacher::where('group_id', $groupId)
                    ->where('subject_id', $request->subject_id)
                    ->where('teacher_id', $request->teacher_id)
                    ->first();
                
                if($groupSubjectTeacher)
                    continue;

                GroupSubjectTeacher::create([
                    'group_id' => $groupId,
                    'subject_id' => $request->subject_id,
                    'teacher_id' => $request->teacher_id
                ]);
            }
            DB::commit();
            return Response::json(['message' => 'Materia asignada correctamente'], 200);
        }catch(\Exception $e){
            DB::rollBack();
            return Response::json(['message' => $e->getMessage()], 500);
        }
    }
}
