<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Group;
use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idCuilapam = Campus::where('campus_number', 32)->first()->id;
        $idTule = Campus::where('campus_number', 4)->first()->id;
        for ($i = 136; $i <= 140; $i++) {

            Group::create([
                'name' => $i,
                'campus_id' => $idTule,
                'level_id' => Level::where('name', 'Primero')->where('campus_id', $idTule)->first()->id,
            ]);

        }

        for ($i = 336; $i <= 340; $i++) {

            Group::create([
                'name' => $i,
                'campus_id' => $idTule,
                'level_id' => Level::where('name', 'Primero')->where('campus_id', $idTule)->first()->id,
            ]);

        }

        for ($i = 536; $i <= 540; $i++) {

            Group::create([
                'name' => $i,
                'campus_id' => $idTule,
                'level_id' => Level::where('name', 'Primero')->where('campus_id', $idTule)->first()->id,
            ]);

        }
    }
}
