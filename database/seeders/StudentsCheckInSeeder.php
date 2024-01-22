<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StudentCheckIn;
use App\Models\Student;
use Faker\Factory as Faker;

class StudentsCheckInSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Obtener todos los estudiantes existentes
        $studentIds = Student::pluck('id')->toArray();

        // Crear registros de salida de estudiantes de prueba utilizando el modelo StudentCheckOut
        for ($i = 0; $i < 10; $i++) {
            $createdAt = $faker->dateTimeBetween('-1 day', '11:59:59');
            
            // Seleccionar un ID de estudiante aleatorio de los existentes
            $randomStudentId = $faker->randomElement($studentIds);

            StudentCheckIn::create([
                'student_id' => $randomStudentId,
                'created_at' => $createdAt,
            ]);
        }
    }
}