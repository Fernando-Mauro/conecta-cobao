<?php

namespace App\Http\Controllers\v1\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Traits\StudentTrait;

class StudentRegistrationController extends Controller
{
    use StudentTrait;
    public function registerStudent(Request $request)
    {
        if (!$this->isValidEnrollment($request->input('enrollment'))) {
            return Response::json(["message" => "Matricula invalida"], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'group' => 'required|integer',
            'campus' => 'required|integer',
            'enrollment' => [
                'required',
                // Rule::unique('students', 'enrollment')->whereNull('deleted_at')
            ],

            'curp' => [
                'required',
                // Rule::unique('students', 'curp')->whereNull('deleted_at')
            ],

        ]);
        
        if ($validator->fails()) {
            return Response::json(["message" => 'Error en el formato de los datos'], 400);
        }
        
        $enrollment = $request->input('enrollment');
        $name = $request->input('name');
        $phone = $request->input('phone');
        $group = $request->input('group');
        $campus = $request->input('campus');
        $curp = $request->input('curp');

        $student = new Student();
        $student->enrollment = $enrollment;
        $student->curp = $curp;
        $student->name = $name;
        $student->phone = $phone;
        $student->group = $group;
        $student->campus = $campus;

        $student->save();

        return Response::json([
            'message' => 'Estudiante registrado correctamente'
        ], 200);
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
                $student = new Student();
                $student->enrollment = $studentRequest['matricula'];
                $student->curp = $studentRequest['curp'];
                $student->name = $studentRequest['nombre'];
                $student->phone = $studentRequest['telefono'];
                $student->group = $studentRequest['grupo'];
                $student->campus = $studentRequest['plantel'];
                $student->save();
            }

            return Response::json([
                'message' => 'Estudiante registrado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'message' => 'Error al enviar los estudiantes'
            ], 404);
        }
    }
}
