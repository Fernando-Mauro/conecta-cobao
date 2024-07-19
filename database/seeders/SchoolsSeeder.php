<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        School::create([
            'name' => 'Colegio de Bachilleres del Estado de Oaxaca',
            'address' => 'Avenida Universidad 145, Universidad, Exhacienda Candiani, 71230 Santa Cruz Xoxocotlán, Oax.',
            'city' => 'Oaxaca de Juárez',
            'state' => 'Oaxaca',
            'country' => 'México'
        ]);
    }
}
