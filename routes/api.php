<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

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

Route::post('tracker', function (Request $request) {
    // Log to tracker channel after convert to string
    $info = json_encode($request->all());
    Log::channel('daily')->info($info);
    Telegram::sendMessage([
        'chat_id' => '5720406551',
        'text' => $info
    ]);
    return response()->json(['message' => 'success']);
});

Route::prefix('v1')->group(function () {
    Route::prefix('/admins')->group(function () {
        Route::middleware('jwt.verify', 'role:admin')->group(function () {
            Route::post('/', 'App\Http\Controllers\v1\Admin\AdminRegistrationController@registerAdmin');
            Route::post('/massiveLoad', 'App\Http\Controllers\v1\Admin\AdminRegistrationController@registerAdmins');
            Route::get('/', 'App\Http\Controllers\v1\Admin\AdminController@getAllAdmins');
            Route::post('/massiveMessage', 'App\Http\Controllers\v1\Admin\AdminController@sendMassiveMessages');
            // Get, edit and delete admin
            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'App\Http\Controllers\v1\Admin\AdminController@getAdminById');
                Route::patch('/', 'App\Http\Controllers\v1\Admin\AdminController@editAdminById');
                Route::delete('/', 'App\Http\Controllers\v1\Admin\AdminController@deleteAdminById');
            });
        });
        Route::middleware('jwt.verify', 'role:admin,teacher')->group(function () {
            Route::get('/getGroupsBySemester/{semester}', 'App\Http\Controllers\v1\Admin\AdminController@getGroups');
            Route::get('/getGroupsByLevelId/{levelId}', 'App\Http\Controllers\v1\Admin\AdminController@getGroupsByLevelId');
        });
    });

    Route::middleware(['jwt.verify', 'role:admin'])->group(function () {
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
        Route::middleware('jwt.verify', 'role:admin')->group(function () {
            Route::get('/getAllStudentsByCampus', 'App\Http\Controllers\v1\Student\StudentController@getAllStudentsByCampus');
            Route::post('/', 'App\Http\Controllers\v1\Student\StudentRegistrationController@registerStudent');
            Route::post('/massiveLoad', 'App\Http\Controllers\v1\Student\StudentRegistrationController@registerStudents');
            Route::patch('/{id}', 'App\Http\Controllers\v1\Student\StudentEditController@editStudentById');
            Route::delete('{id}', 'App\Http\Controllers\v1\Student\DeleteStudentController@deleteStudentById');
        });

        Route::middleware(['jwt.verify', 'role:tutor,admin,teacher'])->group(function () {
            Route::get('{id}', 'App\Http\Controllers\v1\Student\StudentController@getStudentById');
        });

        Route::middleware(['jwt.verify', 'role:teacher,admin'])->group(function () {
            Route::get('/{id}/lastCheckIn', 'App\Http\Controllers\v1\Student\StudentController@getLasCheckInById');
            Route::get('/group/{group}', 'App\Http\Controllers\v1\Student\StudentController@getStudentsByGroup');
        });

        Route::middleware('jwt.verify', 'role:admin')->group(function () {
            Route::get('{enrollment}/checksByPeriod', 'App\Http\Controllers\v1\Student\StudentController@getChecksByPeriod');
            Route::get('{identifier}/register_check', 'App\Http\Controllers\v1\Student\StudentCheckController@registerStudentCheck');
        });
    });

    Route::prefix('/checks')->group(function () {
        Route::middleware(['jwt.verify', 'role:tutor'])->group(function () {
            Route::get('/', 'App\Http\Controllers\v1\Student\StudentController@checksByTutor');
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

        Route::middleware('jwt.verify')->group(function () {
            Route::get('validate', 'App\Http\Controllers\v1\Auth\AuthController@validateToken');
            Route::post('resetPassword', 'App\Http\Controllers\v1\Auth\AuthController@resetPassword');
        });
    });

    Route::prefix('/justifications')->group(function () {

        Route::middleware('jwt.verify', 'role:tutor')->group(function () {
            Route::post('/', 'App\Http\Controllers\v1\Justification\JustificationController@postJustification');
        });

        // Route::get('/media/{fileName}', 'App\Http\Controllers\v1\Justification\JustificationController@getJustificationFile');
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
            Route::post('/', 'App\Http\Controllers\v1\Teachers\TeachersRegistrationController@registerTeacher');
            Route::post('/assignSubject', 'App\Http\Controllers\v1\Teachers\TeachersController@assignSubject');
            Route::post('/massiveLoad', 'App\Http\Controllers\v1\Teachers\TeachersRegistrationController@registerTeachers');
            Route::get('/{id}', 'App\Http\Controllers\v1\Teachers\TeachersController@getTeacherById');
            Route::patch('/{id}', 'App\Http\Controllers\v1\Teachers\TeachersController@editTeacherById');
            Route::delete('/{id}', 'App\Http\Controllers\v1\Teachers\TeachersController@deleteTeacherById');
        });
    });

    Route::middleware('jwt.verify', 'role:admin')->group(function () {
        Route::prefix('/tutors')->group(function () {
            Route::post('/', 'App\Http\Controllers\v1\Tutor\TutorsRegistrationController@registerTutor');
            Route::post('/massiveLoad', 'App\Http\Controllers\v1\Tutor\TutorsRegistrationController@registerTutors');
            Route::get('/group/{group}', 'App\Http\Controllers\v1\Tutor\TutorController@getTutorsByGroup');
            Route::get('/{id}', 'App\Http\Controllers\v1\Tutor\TutorController@getTutorById');
            Route::patch('/{id}', 'App\Http\Controllers\v1\Tutor\TutorController@editTutorById');
            Route::delete('/{id}', 'App\Http\Controllers\v1\Tutor\DeleteTutorController@deleteTutorById');
        });
    });

    Route::middleware(['jwt.verify', 'role:admin'])->group(function () {
        Route::apiResource('subjects', \App\Http\Controllers\v1\Subject\SubjectController::class);
        Route::apiResource('levels', \App\Http\Controllers\v1\Level\LevelController::class);
        Route::get('levels/{level}/subjects', [\App\Http\Controllers\v1\Level\LevelController::class, 'getSubjects']);
        Route::get('levels/{level}/groups', [\App\Http\Controllers\v1\Level\LevelController::class, 'getGroups']);
    });

    Route::middleware(['jwt.verify', 'role:admin'])->group(function () {
        Route::prefix('levels')->group(function () {
            Route::get('levels', 'App\Http\Controllers\v1\Level\LevelController@getLevels');
            // Route::get('/{level}/groups', 'App\Http\Controllers\v1\Level\LevelController@getGroups');
        });
    });

    Route::middleware(['jwt.verify', 'role:admin'])->group(function () {
        require __DIR__ . '/apiRoutes/users.php';
    });

    Route::middleware(['jwt.verify', 'role:admin'])->group(function () {
        Route::prefix("migrations")->group(function () {
            Route::post('semesters', 'App\Http\Controllers\v1\Migrations\MigrationController@runSemesterMigration');
        });
    });

    Route::middleware(['jwt.verify', 'role:admin'])->group(function () {
        Route::prefix('stats')->group(function () {
            Route::prefix('students')->group(function () {
                // TODO:
                // Route::get('byGroup', 'App\Http\Controllers\v1\Stats\StatsController@getStudentsByGroup');
                // Route::get('byLevel', 'App\Http\Controllers\v1\Stats\StatsController@getStudentsByLevel');
                // Route::get('bySemester', 'App\Http\Controllers\v1\Stats\StatsController@getStudentsBySemester');
                Route::get('byCampus', 'App\Http\Controllers\v1\Student\StatsController@getStatsByCampus');
            });
        });
    });
});
