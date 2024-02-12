<?php

namespace App\Http\Controllers\v1\Student;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use App\Traits\StudentTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    use StudentTrait;

    public function getStudentById($id): JsonResponse
    {
        $student = Student::where('id', $id)->select('curp', 'name', 'phone', 'enrollment', 'id', 'group')->first();

        if (!$student) {
            return Response::json(['message' => 'Estudiante no encontrado'], 404);
        }

        $user = Auth::user();
        $tutorStudent = TutorStudent::where('student_id', $student->id)->first();

        // Verifica si la relación TutorStudent existe
        if (!$tutorStudent) {
            return Response::json(['message' => 'Tutor no encontrado'], 404);
        }

        $tutor = Tutor::where('id', $tutorStudent->tutor_id)->first();

        // Verifica si el tutor existe
        if (!$tutor) {
            return Response::json(['message' => 'Tutor no encontrado'], 404);
        }

        if ($user->hasRole('admin') || $user->hasRole('teacher')) {
            $tutorData = ['tutor_name' => $tutor->name, 'tutor_phone' => $tutor->phone];
            return Response::json(array_merge($student->toArray(), $tutorData), 200);
        }

        $activeTutor = $tutorStudent->activeTutor()->first();

        if ($activeTutor->id != $tutor->id) {
            return Response::json(['message' => 'No tienes permiso para acceder a estos datos'], 403);
        }

        return Response::json([$student], 200);
    }

    public function checksByTutor()
    {
        $user = Auth::user();

        if (!$user) {
            return Response::json(['message' => 'Invalid credentials'], 401);
        };
        $userId = $user->id;
        $tutor = Tutor::where('user_id', $userId)->first();
        Log::channel('daily')->debug($userId);

        $tutorStudents = TutorStudent::where('tutor_id', $tutor->id)->get();

        $studentsEntryAndExit = [];
        foreach ($tutorStudents as $tutorStudent) {
            $student = Student::where('id', $tutorStudent->student_id)->select('name', 'group', 'id')->first();

            $studentEntryAndExit = Student::with([
                'checkIns' => function ($query) {
                    $query->orderBy('created_at', 'desc')->take(7);
                },
                'checkOuts' => function ($query) {
                    $query->orderBy('created_at', 'desc')->take(7);
                }
            ])->where('id', $student->id)->select('created_at', 'name', 'id', 'group')->first();

            if (!$studentEntryAndExit) {
                continue;
            }
            array_push($studentsEntryAndExit, $studentEntryAndExit);
        }

        if (empty($studentsEntryAndExit)) {
            return Response::json(['message' => 'Registros no encontrados'], 404);
        }
        return Response::json($studentsEntryAndExit, 200);
    }


    public function getAllStudentsByCampus()
    {
        $user = Auth::user();

        if (!$user) {
            return Response::json(['message' => 'Invalid credentials'], 401);
        }
        $admin = Admin::where('user_id', $user->id)->first();
        $students = Student::where('campus', $admin->campus)->select('name', 'group', 'campus', 'enrollment')->get();
        return Response::json($students, 200);
    }
    public function getStudentsByGroup($group): JsonResponse
    {
        $students = Student::where('group', $group)
            ->selectRaw('id, name as nombre, curp, enrollment as matricula, phone as telefono')
            ->get();
        return Response::json($students, 200);
    }


    public function getChecksByPeriod(Request $request, $enrollment): JsonResponse
    {
        Log::channel('daily')->info('isValidEnrollment');
        $startPeriod = Carbon::parse($request->startPeriod)->startOfDay();
        $endPeriod = Carbon::parse($request->endPeriod)->endOfDay();

        // Consulta los registros de entradas y salidas dentro del período
        $studentEntryAndExit = Student::with([
            'checkIns' => function ($query) use ($startPeriod, $endPeriod) {
                $query->whereBetween('created_at', [$startPeriod, $endPeriod]);
            },
            'checkOuts' => function ($query) use ($startPeriod, $endPeriod) {
                $query->whereBetween('created_at', [$startPeriod, $endPeriod]);
            }
        ])
            ->where('enrollment', $enrollment)
            ->first();

        if (!$studentEntryAndExit) {
            return Response::json(['message' => 'Registros no encontrados'], 404);
        }
        Log::channel('daily')->debug('👌');
        return Response::json([$studentEntryAndExit], 200);
    }
}
