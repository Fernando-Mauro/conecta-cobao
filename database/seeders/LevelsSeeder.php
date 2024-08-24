<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            'Primero',
            'Segundo',
            'Tercero',
            'Cuarto',
            'Quinto',
            'Sexto'
        ];

        foreach ($levels as $level) {
            Level::create([
                'name' => $level,
                'campus_id' => 1
            ]);
            
            Level::create([
                'name' => $level,
                'campus_id' => 2
            ]);
        }
    }
}
