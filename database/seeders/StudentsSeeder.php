<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use Illuminate\Support\Facades\File;

class StudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = fopen(public_path('Datos307completos.csv'), 'r');
        $firstLine = true;
        while(($data=fgetcsv($csvFile,2000, ",")) != FALSE){
            if(!$firstLine){
                Student::create([
                    'enrollment'=>$data[0],
                    'name'=>$data[1],
                    'phone'=>$data[2],
                    'curp'=>$data[3],
                    'group'=>$data[4],
                    'campus'=>$data[5],
                ]);
            }
            $firstLine = false;
        }
    }
}
