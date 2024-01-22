<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TutorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();

        foreach ($students as $student) {
            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
            
            $user = User::create([
                'name' => strtolower(str_replace(' ', '', $student->name)) . ' tutor',
                'email' => $student->enrollment,
                'password' => Hash::make($password)
            ]);

            Tutor::create([
                'name' => $user->name,
                'phone' => $student->phone,
                'telegram_chat_id' => null,
                'active' => true,
                'user_id' => $user->id, // Aquí es donde estableces la relación con el usuario
            ]);

            $info = "Nombre: {$user->name}, Correo: {$user->email}, Contraseña: {$password}, Curp: {$student->curp}\n";
            file_put_contents('tutors.txt', $info, FILE_APPEND);

            $roleName = "tutor"; // Suponiendo que el rol se envía en la solicitud
            $role = Role::where('name', $roleName)->first();
            $user->assignRole($role);
        }
    }
}
