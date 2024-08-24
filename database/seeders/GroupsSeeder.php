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
        for ($i = 101; $i < 136; $i++) {
            if ($i >= 111 && $i <= 130)
                continue;

            Group::create([
                'name' => $i,
                'campus_id' => $idCuilapam,
                'level_id' => Level::where('name', 'Primero')->where('campus_id', $idCuilapam)->first()->id,
            ]);

            Group::create([
                'name' => $i,
                'campus_id' => $idTule,
                'level_id' => Level::where('name', 'Primero')->where('campus_id', $idTule)->first()->id,
            ]);
        }

        for ($i = 301; $i < 336; $i++) {
            if ($i >= 311 && $i <= 330)
                continue;

            Group::create([
                'name' => $i,
                'campus_id' => $idCuilapam,
                'level_id' => Level::where('name', 'Tercero')->where('campus_id', $idCuilapam)->first()->id,
            ]);

            Group::create([
                'name' => $i,
                'campus_id' => $idTule,
                'level_id' => Level::where('name', 'Tercero')->where('campus_id', $idTule)->first()->id,
            ]);
        }

        for ($i = 501; $i < 535; $i++) {
            if ($i >= 511 && $i <= 530)
                continue;

            Group::create([
                'name' => $i,
                'campus_id' => $idCuilapam,
                'level_id' => Level::where('name', 'Quinto')->where('campus_id', $idCuilapam)->first()->id,
            ]);

            Group::create([
                'name' => $i,
                'campus_id' => $idTule,
                'level_id' => Level::where('name', 'Quinto')->where('campus_id', $idTule)->first()->id,
            ]);
        }
    }
}
