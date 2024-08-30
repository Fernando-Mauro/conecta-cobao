<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('/users')->group(function(){
    Route::get('/count', 'App\Http\Controllers\v1\User\UserController@countUsers');
});