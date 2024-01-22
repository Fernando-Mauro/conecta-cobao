<?php

namespace App\Http\Controllers\v1\Report;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function postReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer',
            'description' => 'required|string',
            'subject' => 'required|string'
        ]);

        if ($validator->fails()) {
            return Response::json(['message' => 'Datos invalidos'], 400);
        }

        $id = Auth::id();

        if (!$id) {
            return Response::json(['message' => 'Credenciales incorrectas'], 400);
        }

        Report::create([
            'student_id' => $request->input('student_id'),
            'user_id' => $id,
            'description' => $request->input('description'),
            'subject' => $request->input('subject')
        ]);
        return Response::json(['message' => 'Reporte creado correctamente'], 200);
    }

    public function getReportByStudentId($id)
    {
        $sixMonthsAgo = \Carbon\Carbon::now()->subMonths(6);
        $reports = Report::where('student_id', $id)
            ->where('created_at', '>=', $sixMonthsAgo)
            ->get();

        if ($reports->isEmpty()) {
            return Response::json(['message' => 'No hay reportes para este estudiante en los últimos 6 meses'], 200);
        }

        $reports = $reports->map(function ($report) {
            return [
                'id' => $report->id,
                'fecha' => $report->created_at->toDateTimeString(),
                'descripción' => $report->description,
                'materia' => $report->subject,
            ];
        });

        return Response::json($reports, 200);
    }

    public function getreportsByPeriod(Request $request)
    {
        sleep(3);
        $validator = Validator::make($request->query(), [
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        if ($validator->fails()) {
            return Response::json(['message' => 'Error en el formato de datos'], 400);
        }

        $start = $request->query('start');
        $end = $request->query('end');

        $reports = Report::whereBetween('created_at', [$start, $end])->select('id', 'student_id', 'description', 'subject', 'created_at')->get();


        $reports = $reports->map(function ($report) {
            $student = Student::find($report->student_id);
            return [
                'id' => $report->id,
                'fecha' => $report->created_at->format('d-m-Y H:i:s'),
                'estudiante' => $student->name,
                'descripción' => $report->description,
                'materia' => $report->subject,
            ];
        });

        return Response::json($reports, 200);
    }
}
