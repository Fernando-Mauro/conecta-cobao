<?php

namespace App\Http\Controllers\v1\migrations;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Group;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrationController extends Controller
{
    public function runSemesterMigration()
    {
        DB::beginTransaction();
        try{

            $userId = Auth::id();
            $campusId = Admin::where('user_id', $userId)->first()->campus_id;
    
            $groups = Group::where('campus_id', $campusId)->get();
            $levels = Level::where('campus_id', $campusId)->get();
            
            $keys = array(
                "Primero" => "Segundo",
                "Segundo" => "Tercero",
                "Tercero" => "Cuarto",
                "Cuarto" => "Quinto",
                "Quinto" => "Sexto",
                "Sexto" => "Egresado"
            );
    
            foreach($groups as $group) {
                $currentLevel = $levels->where('id', $group->level_id)->first();
                if($currentLevel->name == "Sexto"){
                    Log::info('El grupo ' . $group->name . ' no se puede mover de nivel');
                    continue;
                }
                $nextLevel = $levels->where('name', $keys[$currentLevel->name])->first();
                
                if($nextLevel ) {
                    Log::info('El grupo ' . $group->name . ' se movió al nivel ' . $nextLevel->name . ' venia del nivel ' . $currentLevel->name);
                    $groups->name = $group->name += 100;
                    $group->level_id = $nextLevel->id;
                    $group->save();
                }
            }
            DB::commit();
            return response()->json(["message" => "Migración de semestres completada"], 200);
        }catch(\Exception $e){
            Log::error('Error al migrar los semestres: ' . $e->getMessage());
            DB::rollBack();
            return response()->json(["message" => "Error al migrar los semestres"], 500);
        }

    }
}
