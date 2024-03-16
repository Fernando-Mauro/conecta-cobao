<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Campus::create([
            'name' => 'Cuilápam',
            'campus_number' => 32,
            'address' => 'Prolongación 2 de Abril S/N, Centro, 71403, Cuilápam de Guerrero, Oaxaca'
        ]);
    }
}
