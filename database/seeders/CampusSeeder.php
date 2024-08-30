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
            'address' => 'Prolongación 2 de Abril S/N, Centro, 71403, Cuilápam de Guerrero, Oaxaca',
            'city' => "Cuilápam de Guerrero",
            'school_id' => 1,
            'active' => true
        ]);

        // The campus for the tule
        Campus::create([
            'name' => 'El Tule',
            'campus_number' => 4,
            'address' => 'C. Cam. Nacional 2, 6ta Etapa IVO Fracc el Retiro, 68297 Santa María del Tule, Oax.',
            'city' => "Santa María del Tule",
            'school_id' => 1,
            'active' => true
        ]);
    }
}
