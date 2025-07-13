<?php

namespace Database\Seeders;

use App\Models\Owner_category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Owner_CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner_types = [
          ['name' => 'Accommodation'],
          ['name' => 'Airlines Company'],
          ['name' => 'Tourism Company'],
          ['name' => 'Vehicle Owner'],
          ['name' => 'Activity Owner'],
        ];

        foreach ($owner_types as $type) {
            Owner_category::query()->create($type);
        }
    }
}
