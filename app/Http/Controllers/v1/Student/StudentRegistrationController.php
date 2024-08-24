<?php

namespace App\Http\Controllers\v1\Student;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Traits\StudentTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class StudentRegistrationController extends Controller
{
    use StudentTrait;

    public function createStudent($name, $phone, $groupNumber, $enrollment, $curp)
    {
        $admin = Auth::user();
        $campusId = Admin::where('user_id', $admin->id)->first()->campus_id;
    
        $groupId = Group::where('name', $groupNumber)->where('campus_id', $campusId)->first()->id;
        
        $user = User::create([
            'name' => $name,
            'email' => $enrollment,
            'password' => Hash::make($curp),
        ]);
    
        Student::create([
            'phone' => $phone,
            'group_id' => $groupId,
            'campus_id' => $campusId,
            'enrollment' => $enrollment,
            'curp' => $curp,
            'user_id' => $user->id
        ]);
    }

    public function registerStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'group' => 'required|integer',
            'enrollment' => ['required'],
            'curp' => ['required']
        ]);
        
        if ($validator->fails()) {
            return Response::json(["message" => 'Error en el formato de los datos'], 400);
        }
        
        if (!$this->isValidEnrollment($request->input('enrollment'))) {
            return Response::json(["message" => "Matricula invalida"], 400);
        }
    
        try {
            $name = $request->input('name');
            $phone = $request->input('phone');
            $groupNumber = $request->input('group');
            $enrollment = $request->input('enrollment');
            $curp = $request->input('curp');
            $this->createStudent($name, $phone, $groupNumber, $enrollment, $curp);
    
            return Response::json([
                'message' => 'Estudiante registrado correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::channel('daily')->error('Error al crear los estudiantes: ' . $e->getMessage());
            return Response::json([
                'message' => 'Error al crear el estudiante: ' . $e->getMessage()
            ], 400);
        }
    }

    public function registerStudents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.nombre' => 'required|string',
            '*.telefono' => 'required|string',
            '*.grupo' => 'required|integer',
            '*.matricula' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidEnrollment($value)) {
                        $fail($attribute . ' es inválido.');
                    }
                }
            ],
            '*.curp' => 'required',
        ]);
    
        if ($validator->fails()) {
            return Response::json(["message" => 'Error en el formato de los datos'], 400);
        }
    
        try {
            foreach ($request->all() as $studentRequest) {
                Log::channel('daily')->info('voy a intentar crear un student');
                $name = $studentRequest['nombre'];
                $phone = $studentRequest['telefono'];
                $groupNumber = $studentRequest['grupo'];
                $enrollment = $studentRequest['matricula'];
                $curp = $studentRequest['curp'];
                $this->createStudent($name, $phone, $groupNumber, $enrollment, $curp);
            }
    
            return Response::json([
                'message' => 'Estudiantes registrados correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::channel('daily')->error('Error al crear los estudiantes: ' . $e->getMessage());
            return Response::json([
                'message' => 'Error al crear los estudiantes: ' . $e->getMessage()
            ], 400);
        }
    }
}
