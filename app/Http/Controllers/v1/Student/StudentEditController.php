<?php

namespace App\Http\Controllers\v1\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use App\Traits\StudentTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class StudentEditController extends Controller
{
    use StudentTrait;

    public function editStudentById($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'curp' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidCurp($value)) {
                        $fail($attribute . ' es invalido.');
                    }
                }
            ],
            'enrollment' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidEnrollment($value)) {
                        $fail($attribute . ' es invalido.');
                    }
                }
            ],
            'group' => 'required|integer',
            'phone' => 'required|string',
            'tutor_name' => 'required|string',
            'tutor_phone' => 'required|string'
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages()->all();
            $messageStr = implode(" ", $messages);
            return Response::json(["message" => $messageStr], 400);
        }

        // $id = $request->input('id');

        $student = Student::where('id', $id)->first();
        $student->update($request->only('name', 'curp', 'enrollment', 'group', 'phone'));

        $tutorStudent = TutorStudent::where('student_id', $id)->first();
        $tutor = Tutor::where('id', $tutorStudent->tutor_id);
        
        $tutorData = [
            'name' => $request->input('tutor_name'),
            'phone' => $request->input('tutor_phone')
        ];
        $tutor->update($tutorData);

        return Response::json(["message" => 'Los datos se han actualizado correctamente'], 200);
    }
}
