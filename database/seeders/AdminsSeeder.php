<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminsData = [ // Aquí puedes agregar los datos de tus maestros
            [
                'nombre' => 'Fernando Francisco López Mauro',
                'telefono' => '9513947132',
                'correo' => 'fermaurolf@gmail.com',
                'campus' => 32
            ],
            [
                'nombre' => 'Fernando Francisco López Mauro',
                'telefono' => '9513947132',
                'correo' => 'lopezmauro1973f@gmail.com',
                'campus' => 4
            ],
        ];

        foreach($adminsData as $admin){
            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);

            $user = User::create([
                'name' => $admin['nombre'],
                'email' => $admin['correo'],
                'password' => Hash::make($password)
            ]);

            
            Admin::create([
                'phone' => $admin['telefono'],
                'campus_id' => Campus::where('campus_number', $admin['campus'])->first()->id,
                'user_id' => $user->id
            ]);
            
            $info = "Nombre: {$user->name}, Correo: {$user->email}, Contraseña: {$password}, Plantel: {$admin['campus']} \n";
            file_put_contents('admins.txt', $info, FILE_APPEND);

            $roleName = "admin";
            
            $role = Role::where('name', $roleName)->first();

            $user->assignRole($role);
        }
    }
}
