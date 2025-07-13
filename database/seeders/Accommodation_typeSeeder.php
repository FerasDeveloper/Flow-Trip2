<?php

namespace Database\Seeders;

use App\Models\Accommodation_type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Accommodation_typeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accommodationTypes = [
            ['name' => 'Hotel'],
            ['name' => 'Motel'],
            ['name' => 'Villa'],
            ['name' => 'Apartment'],
            ['name' => 'Guest House'],
            ['name' => 'Cottage'],
            ['name' => 'Cabin'],
            ['name' => 'Farm Stay'],
            ['name' => 'Treehouse'],
        ];

        foreach ($accommodationTypes as $type) {
            Accommodation_type::query()->create($type);
        }
    }
}
