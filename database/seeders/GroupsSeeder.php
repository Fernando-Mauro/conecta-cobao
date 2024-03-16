<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Groups;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i = 201; $i < 215; $i++){
            Groups::create([
                'name' => $i,
                'campus_id' => Campus::where('campus_number', 32)->first()->id,
            ]);
        }

        for($i = 401; $i < 413; $i++){
            Groups::create([
                'name' => $i,
                'campus_id' => Campus::where('campus_number', 32)->first()->id,
            ]);
        }

        for($i = 601; $i < 613; $i++){
            Groups::create([
                'name' => $i,
                'campus_id' => Campus::where('campus_number', 32)->first()->id,
            ]);
        }
    }
}
