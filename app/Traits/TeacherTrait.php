<?php

namespace App\Traits;

trait TeacherTrait{
    public function isValidEmail($email)
    {
        if (!preg_match('/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/', $email))
            return false;
        return true;
    }
}