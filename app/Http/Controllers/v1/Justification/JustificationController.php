<?php

namespace App\Http\Controllers\v1\Justification;

use App\Http\Controllers\Controller;
use App\Models\Justification;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use App\Traits\StudentTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Laravel\Facades\Telegram;

class JustificationController extends Controller
{
    use StudentTrait;
    public function postJustification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'curp' => 'required|string',
            'oficio' => 'required|mimes:jpeg,png,jpg',
            'receta' => 'required|mimes:jpeg,png,jpg',
            'ine' => 'required|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return Response::json(['message' => 'Formato de datos invalidos'], 400);
        }

        if(!$this->isValidCurp($request->input('curp'))){
            return Response::json(['message' => 'Curp invalida'], 400);
        }
        // Obtén los archivos de las imágenes
        $office = $request->file('oficio');
        $recipe = $request->file('receta');
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
        
        if(!$tutor){
            return Response::json(['message' => 'No se encontró el tutor'], 404);
        }
        // Obtener el estudiante por su curp
        $student = Student::where('curp', $request->input('curp'))->first();

        if (!$student) {
            return Response::json(['error' => 'No se encontró al estudiante'], 404);
        }

        $tutorStudent = TutorStudent::where('student_id', $student->id)->first();

        if (!$tutorStudent || $tutorStudent->tutor_id != $tutor->id) {
            return Response::json(['message' => 'No tienes permiso para acceder a estos datos'], 403);
        }

        $justification = Justification::create([
            'student_id' => $student->id,
            'files_names' => $fileNames,
            'tutor_id' => $tutor->id,
            'start_date' => $request->input('startDate'),
            'end_date' => $request->input('endDate'),
            'campus_id' => $tutor->campus_id,
            'active' => true,
            'approved' => null,
        ]);

        return Response::json(['message' => 'Justificante creado exitosamente', 'id' => $justification->id ], 200);
    }


    public function getJustifications(Request $request): JsonResponse
    {
        $campus_id = Auth::user()->admin->campus_id;

        $justifications = Justification::where('campus_id', $campus_id)->orderBy('created_at', 'desc')->get();

        $justifications->transform(function ($justification){
            return [
                'id' => $justification->id,
                'Estudiante' => $justification->student->user->name,
                'Tutor' => $justification->tutor->user->name,
                'Estatus' => $justification->approved === null ? 'Pendiente' : ($justification->approved ? 'Aceptado' : 'Rechazado')
            ];
        });

        return Response::json($justifications, 200);
    }

    public function getJustificationById($id): JsonResponse
    {

        $justification = Justification::find($id);
    
        if (!$justification) {
            return response()->json(['error' => 'Justificación no encontrada'], 404);
        }
    
        $student = [
            'name' => $justification->student->user->name,
            'group' => $justification->student->group->name,
        ];
        $tutor = [
            'name' => $justification->tutor->user->name,
            'phone' => $justification->tutor->phone,
        ];
    
        // Obtén los nombres de los archivos de las imágenes
        $fileNames = json_decode($justification->files_names, true);
    
        // Lee los archivos de imagen y codifícalos en base64
        $images = [];

        foreach ($fileNames as $key => $fileName) {
            $path = storage_path('app/justifications/' . $fileName);
            if (File::exists($path)) {
                $fileData = File::get($path);
                $mimeType = File::mimeType($path);
                $images[$key] = 'data:' . $mimeType . ';base64,' . base64_encode($fileData);
            }
        }

        return Response::json([
            'id' => $justification->id,
            'student' => $student,
            'tutor' => $tutor,
            'images' => $images, 
            'start_date' => $justification->start_date,
            'end_date' => $justification->end_date,
            'approved' => $justification->approved,
            'active' => $justification->active,
            'created_at' => $justification->created_at,
            'updated_at' => $justification->updated_at,            
        ],200);
    }
    

    public function editJustificationById($id, Request $request): JsonResponse
    {
        // Validación de datos del request
        $validator = Validator::make([$request->all()], [
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
