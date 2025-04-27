<?php

namespace App\Http\Controllers\v1\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\TutorStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DeleteTutorController extends Controller
{
    public function deleteTutorById($id)
    {
        $tutor = Tutor::where('id', $id)->first();
        
        if(!$tutor){
            return Response::json(['message' => 'Tutor no encontrado'], 404);
        }

        $tutorStudent = TutorStudent::where('tutor_id', $tutor->id)->first();
    
        if($tutorStudent){
            $tutorStudent->delete();
        }
        
        $tutor->delete();
        
        return Response::json(['message' => 'Tutor eliminado correctamente']);
    }
}
