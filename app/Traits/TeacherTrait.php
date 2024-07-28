<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait TeacherTrait{
    public function isValidEmail($email)
    {
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email',
        ]);
    
        return !$validator->fails();
    }
}