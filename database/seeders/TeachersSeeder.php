<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TeachersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachersData = [ // Aquí puedes agregar los datos de tus maestros
            [
                'nombre' => 'Hector Marlon Aguilar Arellanes',
                'telefono' => '9511170780',
                'correo' => 'hectormarlon@gmail.com',
                'campus' => 32
            ],
            [
                'nombre' => 'Cynthia Valeria Gallegos Hernández',
                'telefono' => '9512495959',
                'correo' => 'tecvaleria@gmail.com',
                'campus' => 32
            ],
            [
                'nombre' => 'Ander Imanol López Zarate',
                'telefono' => '9512331826',
                'correo' => 'ander.ima.l.z@gmail.com',
                'campus' => 32
            ]
        ];

        foreach ($teachersData as $teacherData) {
            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);

            $user = User::create([
                'name' => $teacherData['nombre'],
                'email' => $teacherData['correo'],
                'password' => Hash::make($password)
            ]);

            Teacher::create([
                'name' => $user->name,
                'phone' => $teacherData['telefono'],
                'email' => $teacherData['correo'],
                'campus' => $teacherData['campus'],
                'user_id' => $user->id, // Aquí es donde estableces la relación con el usuario
            ]);

            $info = "Nombre: {$user->name}, Correo: {$user->email}, Contraseña: {$password}, Plantel: {$teacherData['campus']} \n";
            file_put_contents('teachers.txt', $info, FILE_APPEND);

            $roleName = "teacher"; // Suponiendo que el rol se envía en la solicitud
            $role = Role::where('name', $roleName)->first();
            $user->assignRole($role);
        }
    }
}
