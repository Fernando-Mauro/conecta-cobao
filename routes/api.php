<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Group;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::prefix('/admins')->group(function () {
        Route::middleware('jwt.verify', 'role:admin')->group(function () {
            Route::post('/', 'App\Http\Controllers\v1\Admin\AdminRegistrationController@registerAdmin');
            Route::post('/massiveLoad', 'App\Http\Controllers\v1\Admin\AdminRegistrationController@registerAdmins');
            Route::get('/', 'App\Http\Controllers\v1\Admin\AdminController@getAllAdmins');
            Route::get('/{id}', 'App\Http\Controllers\v1\Admin\AdminController@getAdminById');
            Route::patch('/{id}', 'App\Http\Controllers\v1\Admin\AdminController@editAdminById');
            Route::delete('/{id}', 'App\Http\Controllers\v1\Admin\AdminController@deleteAdminById');
        });
    });

    Route::middleware(['jwt.verify', 'role:admin'])->group(function(){
        Route::get('/allusers', 'App\Http\Controllers\v1\Admin\AdminController@getAllUsers');
    });

    Route::prefix('/reports')->group(function () {
        Route::middleware('jwt.verify', 'role:admin')->group(function () {
            Route::get('/getreportsByPeriod', 'App\Http\Controllers\v1\Report\ReportController@getreportsByPeriod');
        });
        Route::middleware(['jwt.verify', 'role:admin,teacher'])->group(function () {
            Route::post("/", 'App\Http\Controllers\v1\Report\ReportController@postReport');
            Route::get("/{id}", 'App\Http\Controllers\v1\Report\ReportController@getReportByStudentId');
        });
    });

    Route::prefix('/students')->group(function () {

        Route::middleware(['jwt.verify', 'role:tutor,admin,teacher'])->group(function () {
            Route::get('{id}', 'App\Http\Controllers\v1\Student\StudentController@getStudentById');
        });

        Route::middleware('jwt.verify', 'role:admin')->group(function () {
            Route::post('/', 'App\Http\Controllers\v1\Student\StudentRegistrationController@registerStudent');
            Route::post('/massiveLoad', 'App\Http\Controllers\v1\Student\StudentRegistrationController@registerStudents');
            Route::patch('/{id}', 'App\Http\Controllers\v1\Student\StudentEditController@editStudentById');
            Route::delete('{id}', 'App\Http\Controllers\v1\Student\DeleteStudentController@deleteStudentById');
        });
        
        Route::middleware(['jwt.verify', 'role:teacher,admin'])->group(function () {
            Route::get('/group/{group}', 'App\Http\Controllers\v1\Student\StudentController@getStudentsByGroup');
        });

        Route::middleware('jwt.verify','role:admin')->group(function(){
            // Route::get('register/{enrollment}', 'App\Http\Controllers\v1\Student\StudentController@registerStudentByEnrollment');
            Route::get('{enrollment}/checks', 'App\Http\Controllers\v1\Student\StudentController@checksByEnrollment');
            Route::get('{enrollment}/checksByPeriod', 'App\Http\Controllers\v1\Student\StudentController@getChecksByPeriod');
            Route::get('{enrollment}/register_check', 'App\Http\Controllers\v1\Student\StudentCheckController@registerStudentCheckByEnrollment');
        });    
    });


    Route::prefix('telegram/webhooks')->group(function () {
        Route::post('inbound', 'App\Http\Controllers\v1\Telegram\TelegramController@inbound');
    });

    Route::prefix('/auth')->group(function () {
        Route::post('login', 'App\Http\Controllers\v1\Auth\AuthController@login');
        
        Route::middleware('jwt.verify', 'role:admin')->group(function () {
            Route::post('register', 'App\Http\Controllers\v1\Auth\AuthController@register');
        });
        Route::middleware('jwt.verify', 'role:admin,teacher,tutor')->group(function () {
            Route::post('logout', 'App\Http\Controllers\v1\Auth\AuthController@logout');
            Route::get('user', 'App\Http\Controllers\v1\Auth\AuthController@getAuthenticatedUser');
        });

    });

    Route::prefix('/justifications')->group(function () {

        Route::middleware('jwt.verify', 'role:tutor')->group(function () {
            Route::post('/', 'App\Http\Controllers\v1\Justification\JustificationController@postJustification');
        });

        Route::middleware(['jwt.verify', 'role:admin'])->group(function () {
            Route::get('/', 'App\Http\Controllers\v1\Justification\JustificationController@getJustifications');
            // Route::get('/{id}', 'App\Http\Controllers\v1\Justification\JustificationController@getJustificationById');
            Route::get('/getjustificationsByPeriod', 'App\Http\Controllers\v1\Justification\JustificationController@getjustificationsByPeriod');
            Route::patch('/{id}', 'App\Http\Controllers\v1\Justification\JustificationController@editJustificationById');
        });

        Route::middleware(['jwt.verify', 'role:admin,teacher'])->group(function () {
            Route::get('/{id}', 'App\Http\Controllers\v1\Justification\JustificationController@getJustificationById');
        });

        Route::middleware(['jwt.verify', 'role:teacher'])->group(function () {
            Route::get('/student/{id}', 'App\Http\Controllers\v1\Justification\JustificationController@getActiveJustificationByStudentId');
        });
    });

    Route::prefix('/teachers')->group(function () {
        Route::middleware('jwt.verify', 'role:admin')->group(function () {
            Route::get('/', 'App\Http\Controllers\v1\Teachers\TeachersController@getTeachers');
            Route::get('/{id}', 'App\Http\Controllers\v1\Teachers\TeachersController@getTeacherById');
            Route::post('/', 'App\Http\Controllers\v1\Teachers\TeachersRegistrationController@registerTeacher');
            Route::post('/massiveLoad', 'App\Http\Controllers\v1\Teachers\TeachersRegistrationController@registerTeachers');
            Route::patch('/{id}', 'App\Http\Controllers\v1\Teachers\TeachersController@editTeacherById');
            Route::delete('/{id}', 'App\Http\Controllers\v1\Teachers\TeachersController@deleteTeacherById');
        });
    });

    Route::middleware('jwt.verify', 'role:admin')->group(function () {
        Route::prefix('/tutors')->group(function () {
            Route::get('/{id}', 'App\Http\Controllers\v1\Tutor\TutorController@getTutorById');
            Route::post('/', 'App\Http\Controllers\v1\Tutor\TutorsRegistrationController@registerTutor');
            Route::post('/massiveLoad', 'App\Http\Controllers\v1\Tutor\TutorsRegistrationController@registerTutors');
            Route::get('/group/{group}', 'App\Http\Controllers\v1\Tutor\TutorController@getTutorsByGroup');
            Route::patch('/{id}', 'App\Http\Controllers\v1\Tutor\TutorController@editTutorById');
            Route::delete('/{id}', 'App\Http\Controllers\v1\Tutor\DeleteTutorController@deleteTutorById');
        });
    });
});
