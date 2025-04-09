<?php

namespace App\Http\Controllers\v1\Level;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Campus;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LevelController extends Controller
{
    private function checkPermissions($levelId = null)
    {
        $user = Auth::user();

        $admin = Admin::where("user_id", $user->id)->first();

        if (!$admin) {
            return response()->json([
                'error' => 'Admin not found.'
            ], 404);
        }

        $campus = Campus::find($admin->campus_id);

        if (!$campus) {
            return response()->json([
                'error' => 'Campus not found.'
            ], 404);
        }

        if ($levelId) {
            $level = Level::find($levelId);

            if (!$level || $level->campus_id != $campus->id) {
                return response()->json([
                    'error' => 'Subject not found.'
                ], 404);
            }

            return $level;
        }

        return $campus;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $result = $this->checkPermissions();

        if ($result instanceof \Illuminate\Http\JsonResponse)
            return $result;
        $campus = $result;

        $levels = $campus->levels;

        return response()->json($levels, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // TODO: Implement store() method.
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // TODO: Implement show() method.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // TODO: Implement destroy() method.
    }

    public function getSubjects($levelId)
    {
        
        $level = Level::with('subjects')->findOrFail($levelId);
        
        // Retornar las materias
        return response()->json($level->subjects, 200);
    }

    public function getGroups($levelId)
    {
        $level = Level::with('groups')->findOrFail($levelId);
        
        // Retornar los grupos
        return response()->json($level->groups, 200);
    }
}