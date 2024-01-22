<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StudentCheckOut;
use App\Models\StudentCheckIn;
use Carbon\Carbon;
use Faker\Factory as Faker;

class StudentsCheckOutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Obtener todos los registros de check-in existentes
        $checkIns = StudentCheckIn::all();

        foreach ($checkIns as $checkIn) {
            // Obtener información del registro de check-in
            $studentId = $checkIn->student_id;
            $checkInTime = $checkIn->created_at;

            // Calcular una hora de salida aleatoria después del check-in
            $checkOutTime = Carbon::parse($checkInTime)->addHours(8);

            // Crear un registro de check-out correspondiente
            StudentCheckOut::create([
                'student_id' => $studentId,
                'created_at' => $checkOutTime, // Puedes establecer la misma fecha y hora para created_at
            ]);
        }
    }
}
