<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i = 101; $i < 136; $i++){
            if($i >= 111 && $i <= 130)
                continue;

            Group::create([
                'name' => $i,
                'campus_id' => 1,
                'level_id' => 1,
            ]);
        }

        for($i = 301; $i < 336; $i++){
            if($i >= 311 && $i <= 330)
                continue;

            Group::create([
                'name' => $i,
                'campus_id' => 1,
                'level_id' => 3,
            ]);
        }

        for($i = 501; $i < 535; $i++){
            if($i >= 511 && $i <= 530)
                continue;

            Group::create([
                'name' => $i,
                'campus_id' => 1,
                'level_id' => 5,
            ]);
        }

    }
}
