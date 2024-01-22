<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use Illuminate\Database\Seeder;

class TutorStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtén todos los tutores y estudiantes
        $tutors = Tutor::all();
        $students = Student::all();

        // Itera sobre los tutores y estudiantes y crea registros en la tabla pivote
        $tutorCount = count($tutors);
        $studentCount = count($students);

        // Asegúrate de que tengas al menos un tutor y un estudiante antes de vincularlos
        if ($tutorCount > 0 && $studentCount > 0) {
            for ($i = 0; $i < min($tutorCount, $studentCount); $i++) {
                $tutor = $tutors[$i];
                $student = $students[$i];

                // Crea un registro en la tabla pivote
                TutorStudent::create([
                    'tutor_id' => $tutor->id,
                    'student_id' => $student->id,
                ]);
            }
        }
    }
}
