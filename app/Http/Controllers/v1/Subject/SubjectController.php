<?php

namespace App\Http\Controllers\v1\Subject;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Campus;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    /**
     * Check if the authenticated user has permission to access the given subject.
     */
    private function checkPermissions($subjectId = null)
    {
        $user = Auth::user();

        $admin = Admin::find($user->id);

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

        if ($subjectId) {
            $subject = Subject::find($subjectId);

            if (!$subject || $subject->campus_id != $campus->id) {
                return response()->json([
                    'error' => 'Subject not found.'
                ], 404);
            }

            return $subject;
        }

        return $campus;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $result = $this->checkPermissions();

        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        $campus = $result;

        $subjects = $campus->subjects;
        return response()->json($subjects, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'level_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $result = $this->checkPermissions();

        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        $campus = $result;

        Log::channel('daily')->info('Creating subject', ['name' => $request->name, 'level_id' => $request->level_id]);

        try {
            $subject = Subject::create([
                'name' => $request->name,
                'campus_id' => $campus->id,
                'level_id' => $request->level_id
            ]);

            return response()->json([
                'message' => 'Materia creada exitosamente.',
                'subject' => $subject
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create subject.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $result = $this->checkPermissions($id);

        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        $subject = $result;

        return response()->json($subject, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'level' => 'required|string|max:30'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $result = $this->checkPermissions($id);

        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        $subject = $result;

        try {
            $subject->update([
                'name' => $request->name,
                'level' => $request->level
            ]);

            return response()->json([
                'message' => 'Subject updated successfully.',
                'subject' => $subject
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update subject.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $result = $this->checkPermissions($id);

        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        $subject = $result;

        try {
            $subject->delete();

            return response()->json([
                'message' => 'Materia borrada exitosamente.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete subject.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
