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
use Illuminate\Support\Facades\DB; // Importar para usar transacciones

class StudentRegistrationController extends Controller
{
    use StudentTrait;

    public function createStudent($name, $phone, $groupNumber, $enrollment, $curp)
    {
        // $name = mb_convert_encoding($name, 'UTF-8', 'auto');
        $name = strtoupper(trim(strtr($name, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U'])));
        
        $phone = preg_replace('/\s+/', '', $phone);
        $enrollment = preg_replace('/\s+/', '', $enrollment);
        $curp = strtoupper(preg_replace('/\s+/', '', $curp));
        
        // If the enrollment is empty, it means that the student does not have an enrollment
        if($enrollment != '' && !$this->isValidEnrollment($enrollment)) {
            throw new \Exception('Matricula invalida'.$enrollment);
        }

        if(!$this->isValidCurp($curp)) {
            throw new \Exception('CURP invalido'.$curp);
        }
        
        $admin = Auth::user();
        $campusId = Admin::where('user_id', $admin->id)->first()->campus_id;

        $group = Group::where('name', $groupNumber)->where('campus_id', $campusId)->first();

        if (!$group) {
            throw new \Exception('Grupo no existe');
        }

        $groupId = $group->id;

        $user = User::create([
            'name' => $name,
            'email' => (!$enrollment) ? $curp : $enrollment,
            'password' => Hash::make($curp),
        ]);

        Student::create([
            'phone' => $phone,
            'group_id' => $groupId,
            'campus_id' => $campusId,
            'enrollment' => (!$enrollment) ? NULL : $enrollment,
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
            'curp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json(["message" => 'Error en el formato de los datos'], 400);
        }

        if ($request->input('enrollment') != '' && !$this->isValidEnrollment($request->input('enrollment'))) {
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
            '*.matricula' => 'required',
            '*.curp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json(["message" => 'Error en el formato de los datos'], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($request->all() as $studentRequest) {
                $name = $studentRequest['nombre'];
                $phone = $studentRequest['telefono'];
                $groupNumber = $studentRequest['grupo'];
                $enrollment = $studentRequest['matricula'];
                $curp = $studentRequest['curp'];
                $this->createStudent($name, $phone, $groupNumber, $enrollment, $curp);
            }

            DB::commit();

            return Response::json([
                'message' => 'Estudiantes registrados correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::json([
                'message' => 'Error al crear los estudiantes: ' . $e->getMessage()
            ], 400);
        }
    }
}
