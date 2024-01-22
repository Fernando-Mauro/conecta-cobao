<?php

namespace Database\Seeders;

use App\Models\Message;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Message::create([
            'type' => 'check_in',
            'body' => 'El alumn@ {1} con matricula {2} ha ingresado a las {3} horas',
            'description' => 'Se envia el mensaje al ingresar el alumno a la escuela',
            'active' => true
        ]);
        Message::create([
            'type' => 'check_out',
            'body' => 'El alumn@ {1} con matricula {2} ha salido a las {3} horas',
            'description' => 'Se envia el mensaje al salir el alumno a la escuela',
            'active' => true
        ]);
        Message::create([
            'type' => 'inasistencia',
            'body' => 'El alumn@ {1} con matricula {2} no ha registrado asistencia el dia {3} antes de las {4}',
            'description' => 'Se envia el mensaje cuando un alumno no ingresa antes de las 12 horas ',
            'active' => false
        ]);
    }
}
