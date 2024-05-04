<?php

namespace App\Http\Controllers\v1\Justification;

use App\Http\Controllers\Controller;
use App\Models\Justification;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Laravel\Facades\Telegram;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class JustificationController extends Controller
{
    public function postJustification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required|string',
            'curp' => 'required|string',
            'group' => 'required|string',
            'enrollment' => 'required|string',
            'start' => 'required|date',
            'end' => 'required|date',
            'tutor-name' => 'required|string',
            'office' => 'required|mimes:jpeg,png,jpg',
            'recipe' => 'required|mimes:jpeg,png,jpg',
            'ine' => 'required|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return Response::json(['message' => 'Formato de datos invalidos'], 400);
        }

        Log::channel('daily')->debug(json_encode($request->all()));

        // Obtén los archivos de las imágenes
        $office = $request->file('office');
        $recipe = $request->file('recipe');
        $ine = $request->file('ine');

        $officeName = time() . '_' . $office->getClientOriginalName();
        $recipeName = time() . '_' . $recipe->getClientOriginalName();
        $ineName = time() . '_' . $ine->getClientOriginalName();

        // Guardar las imágenes en la carpeta public/justifications
        $office->storeAs('justifications', $officeName, 'local');
        $recipe->storeAs('justifications', $recipeName, 'local');
        $ine->storeAs('justifications', $ineName, 'local');

        $fileNames = json_encode([
            $officeName,
            $recipeName,
            $ineName,
        ]);

        $userId = Auth::id();
        $tutor = Tutor::where('user_id', $userId)->first();

        // Obtener el estudiante por su matrícula o curp
        $student = Student::where('enrollment', $request->input('enrollment'))
            ->orWhere('curp', $request->input('curp'))
            ->first();

        if (!$student) {
            return Response::json(['error' => 'No se encontró al estudiante'], 404);
        }

        $tutorStudent = TutorStudent::where('student_id', $student->id)->first();

        if (!$tutorStudent || $tutorStudent->tutor_id != $tutor->id) {
            return Response::json(['message' => 'No tienes permiso para acceder a estos datos'], 403);
        }

        // Crear la justificación
        $justification = new Justification([
            'student_id' => $student->id,
            'files_names' => $fileNames,
            'tutor_id' => $tutor->id,
            'start_date' => $request->input('start'),
            'end_date' => $request->input('end'),
            'active' => true, // Puedes personalizar esto según tus necesidades
            'approved' => null, // Puedes establecer esto como nulo por defecto
        ]);

        // Guardar la justificación en la base de datos
        $justification->save();

        return Response::json(['success' => true], 200);
    }


    public function getJustifications(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);

        // Validar los parámetros 'per_page' y 'page' para asegurar que son números positivos
        $validator = Validator::make([
            'per_page' => $perPage,
            'page' => $page,
        ], [
            'per_page' => 'integer|min:1',
            'page' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Error en el formato de los datos'], 400);
        }

        $justifications = Justification::where('active', true)->paginate($perPage, ['*'], 'page', $page);

        $data = [];
        foreach ($justifications->items() as $justification) {
            $student = $justification->student;
            $tutor = $justification->tutor;

            $data[] = [
                'id' => $justification->id,
                'student_id' => $student->id,
                'student_name' => $student->name,
                'tutor_id' => $tutor->id,
                'tutor_name' => $tutor->name,
                // 'tutor_email' => $justification->tutor_email,
                'document_url' => $justification->document_url,
                'start_date' => $justification->start_date,
                'end_date' => $justification->end_date,
                'is_approved' => $justification->approved,
                'is_active' => $justification->active,
                'created_at' => $justification->created_at,
                'updated_at' => $justification->updated_at,
            ];
        }

        return response()->json([
            "data" => $data,
            "meta" => [
                "total" => $justifications->total(),
                "current_page" => $justifications->currentPage(),
                "last_page" => $justifications->lastPage(),
                "per_page" => $perPage,
            ],
        ], 200);
    }
    public function getJustificationById($id): JsonResponse
    {
        // FIXME: COrregir parra no enviar datos a quien no corresponde
        // $id = $request->input('id');
        $justification = Justification::find($id);

        if (!$justification) {
            return response()->json(['error' => 'Justificación no encontrada'], 404);
        }

        $student = $justification->student;
        $tutor = $justification->tutor;

        $data = [
            'id' => $justification->id,
            'student_id' => $student->id,
            'student_name' => $student->name,
            'tutor_id' => $tutor->id,
            'tutor_name' => $tutor->name,
            'tutor_phone' => $tutor->phone,
            'files_names' => $justification->files_names,
            'start_date' => $justification->start_date,
            'end_date' => $justification->end_date,
            'is_approved' => $justification->approved,
            'is_active' => $justification->active,
            'created_at' => $justification->created_at,
            'updated_at' => $justification->updated_at,
        ];
        Log::channel('daily')->debug(json_encode($justification));

        return response()->json([$data]);
    }
    public function editJustificationById($id, Request $request): JsonResponse
    {
        // Validación de datos del request
        $valitador = Validator::make([$request->all()], [
            'approve' => 'required|boolean',
            'observation' => 'required|string'
        ]);

        Log::channel('daily')->debug('intentando aprobar justiciante');
        // Obtener la justificación por ID
        $justification = Justification::find($id);

        if (!$justification) {
            return response()->json(['error' => 'Justificación no encontrada'], 404);
        }

        // Verificar si la justificación ya ha sido aprobada o desaprobada
        if ($justification->approved !== null) {
            return response()->json(['error' => 'La justificación ya ha sido procesada'], 400);
        }

        // Actualizar el estado de aprobación basado en el valor de 'approve'
        $justification->approved = $request->input('approve');
        $justification->save();

        // Obtener datos relacionados para la respuesta
        $student = $justification->student;
        $tutor = $justification->tutor;

        $message = $justification->approved === true ? 'Ha sido aceptada la solicitud de su justificante, pasar a recogerlo a la direccion con su nombre y matricula' : 'Su solicitud ha sido rechazada';
        $observations = $request->input('observation') == '' ? '' : "Observaciones: {$request->input('observation')}";

        // FIXME: Corregir en caso de que no exista el chat_id
        if ($tutor->telegram_chat_id) {
            Telegram::sendMessage([
                'chat_id' => $tutor->telegram_chat_id,
                'text' => "{$message} \n {$observations}",
            ]);
        }

        // Construir la respuesta
        $data = [
            'id' => $justification->id,
            'student_id' => $student->id,
            'student_name' => $student->name,
            'tutor_id' => $tutor->id,
            'tutor_name' => $tutor->name,
            'tutor_email' => $justification->tutor_email,
            'document_url' => $justification->document_url,
            'start_date' => $justification->start_date,
            'end_date' => $justification->end_date,
            'is_approved' => $justification->approved,
            'is_active' => $justification->active,
            'created_at' => $justification->created_at,
            'updated_at' => $justification->updated_at,
        ];

        return Response::json($data, 200);
    }

    public function getActiveJustificationByStudentId($id)
    {
        $sixMonthsAgo = \Carbon\Carbon::now()->subMonths(6);

        $justifications = Justification::where('student_id', $id)
            ->where('approved', true)
            ->where('start_date', '>=', $sixMonthsAgo)
            ->get();

        return Response::json($justifications, 200);
    }

    public function getJustificationByIdTeacher($id)
    {
        $justification = Justification::find($id);

        if (!$justification) {
            return response()->json(['error' => 'Justificación no encontrada'], 404);
        }

        $student = $justification->student;
        $tutor = $justification->tutor;

        $data = [
            'id' => $justification->id,
            'student_id' => $student->id,
            'student_name' => $student->name,
            'tutor_id' => $tutor->id,
            'tutor_name' => $tutor->name,
            // 'tutor_email' => $justification->tutor_email,
            'document_url' => $justification->document_url,
            'start_date' => $justification->start_date,
            'end_date' => $justification->end_date,
            'is_approved' => $justification->approved,
            'is_active' => $justification->active,
            'created_at' => $justification->created_at,
            'updated_at' => $justification->updated_at,
        ];

        return response()->json([$data]);
    }

    public function getjustificationsByPeriod(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        if ($validator->fails()) {
            return Response::json(['message' => 'Error en el formato de datos'], 400);
        }

        $start = $request->query('start');
        $end = $request->query('end');

        $justifications = Justification::whereBetween('created_at', [$start, $end])->where('approved', true)->select('id', 'student_id', 'document_url', 'created_at', 'start_date', 'end_date')->get();


        $justifications = $justifications->map(function ($report) {
            $student = Student::find($report->student_id);
            return [
                'id' => $report->id,
                'Creado' => $report->created_at->format('d-m-Y H:i:s'),
                'Estudiante' => $student->name,
                'Oficio' => $report->document_url,
                'Inicio' => $report->start_date,
                'Final' => $report->end_date,
            ];
        });

        return Response::json($justifications, 200);
    }

    public function getJustificationFile($fileName)
    {
        $path = storage_path('app/justifications/' . $fileName);

        if (!File::exists($path)) {
            return Response::json(['error' => 'Archivo no encontrado'], 404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }
}
