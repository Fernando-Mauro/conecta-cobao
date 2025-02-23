<?php

namespace App\Http\Controllers\v1\Student;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\StudentCheckIn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StatsController extends Controller
{
    public function getStatsByCampus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d', // Validar formato YYYY-MM-DD
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $userId = Auth::user()->id;
        $campusId = Admin::where('user_id', $userId)->first()->campus_id;

        $date = Carbon::parse($request->date)->format('Y-m-d');

        $count = DB::table('student_check_in')
            ->join('students', 'students.id', '=', 'student_check_in.student_id')
            ->where('students.campus_id', $campusId)
            ->whereDate('student_check_in.created_at', $date)
            ->count();

        return response()->json(['count' => $count], 200);
    }
}
