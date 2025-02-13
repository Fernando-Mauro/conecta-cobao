<?php

namespace App\Http\Controllers\v1\migrations;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Group;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MigrationController extends Controller
{
    public function runSemesterMigration()
    {
        $userId = Auth::id();
        $campusId = Admin::where('user_id', $userId)->first()->campus_id;

        $groups = Group::where('campus_id', $campusId)->get();
        $levels = Level::where('campus_id', $campusId)->get();
        
        $keys = array(
            "Primero" => "Segundo",
            "Segundo" => "Tercero",
            "Tercero" => "Cuarto",
            "Cuarto" => "Quinto",
            "Quinto" => "Sexto"
        );

        foreach($groups as $group) {
            $currentLevel = $levels->where('id', $group->level_id)->first();
            $nextLevel = $levels->where('name', $keys[$currentLevel->name])->first();

            if($nextLevel) {
                $groups->name = $group->name += 100;
                $group->level_id = $nextLevel->id;
                $group->save();
            }
            Log::info('El grupo ' . $group->name . ' se movió al nivel ' . $nextLevel->name . ' venia del nivel ' . $currentLevel->name);
        }

        return response()->json(["message" => "Migración de semestres completada"], 200);

    }
}
