<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function countUsers()
    {
        $user = Auth::user();
        $campusId = Admin::where('user_id', $user->id)->first()->campus->id;
        
        $countStudents = Student::where('campus_id', $campusId)->count(); 
        $countTutors = Tutor::where('campus_id', $campusId)->count();
        $countAdmins = Admin::where('campus_id', $campusId)->count();
        $countTeachers = Teacher::where('campus_id', $campusId)->count();
        
        return response()->json([
            'students' => $countStudents,
            'tutors' => $countTutors,
            'admins' => $countAdmins,
            'teachers' => $countTeachers
        ], 200);
    }
}
