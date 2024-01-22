<?php

namespace App\Http\Controllers\v1\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\TutorStudent;
use App\Traits\StudentTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DeleteStudentController extends Controller
{
    use StudentTrait;

    public function deleteStudentById($id)
    {
        $student = Student::where('id', $id)->first();
        
        if(!$student){
            return Response::json(['message' => 'Estudiante no encontrado'], 404);
        }

        $tutorStudent = TutorStudent::where('student_id', $student->id);
        $tutorStudent->delete();
        $student->delete();
        
        return Response::json(['message' => 'Alumno eliminado correctamente']);
    }
}
