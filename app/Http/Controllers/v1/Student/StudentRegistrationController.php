<?php

namespace App\Http\Controllers\v1\Student;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Traits\StudentTrait;
use Illuminate\Support\Facades\Hash;

class StudentRegistrationController extends Controller
{
    use StudentTrait;

    public function createStudent($name, $phone, $groupNumber, $campusNumber, $enrollment, $curp)
    {
        $campusId = Campus::where('campus_number', $campusNumber)->first()->id;
        $groupId = Group::where('name', $groupNumber)->where('campus_id', $campusId)->first()->id;
        $user = User::create([
            'name' => $name,
            'email' => $enrollment,
            'password' => Hash::make($curp),
        ]);

        Student::create([
            'name' => $name,
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
            'campus' => 'required|integer',
            'enrollment' => ['required'],
            'curp' => ['required']
        ]);
        
        if ($validator->fails()) {
            return Response::json(["message" => 'Error en el formato de los datos'], 400);
        }
        
        if (!$this->isValidEnrollment($request->input('enrollment'))) {
            return Response::json(["message" => "Matricula invalida"], 400);
        }
        

        try{

            $enrollment = $request->input('enrollment');
            $name = $request->input('name');
            $phone = $request->input('phone');
            $groupNumber = $request->input('group');
            $campusNumber = $request->input('campus');
            $curp = $request->input('curp');
            $this->createStudent($name, $phone, $groupNumber, $campusNumber, $enrollment, $curp);
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
            '*.plantel' => 'required|integer',
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
                $groupNumber =  $studentRequest['grupo'];
                $campusNumber = $studentRequest['plantel'];
                $enrollment = $studentRequest['matricula'];
                $curp =  $studentRequest['curp'];
                $this->createStudent($name, $phone, $groupNumber, $campusNumber, $enrollment, $curp);
            }

            return Response::json([
                'message' => 'Estudiantes registrado correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::channel('daily')->error('Error al crear el estudiante: ' . $e->getMessage());
            return Response::json([
                'message' => 'Error al crear los estudiantes: ' . $e->getMessage()
            ], 400);
        }
    }
}
