<?php

namespace Database\Seeders;

use App\Models\Car_type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Vehicle_typeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleTypes = [
            'BMW',
            'Mercedes-Benz',
            'Audi',
            'Toyota',
            'Honda',
            'Ford',
            'Chevrolet',
            'Nissan',
            'Hyundai',
            'Kia',
            'Volkswagen',
            'Porsche',
            'Ferrari',
            'Lamborghini',
            'Maserati',
            'Lexus',
            'Infiniti',
            'Acura',
            'Mazda',
            'Subaru',
            'Mitsubishi',
            'Suzuki',
            'Jeep',
            'Dodge',
            'Cadillac',
            'Volvo',
            'Jaguar',
            'Range Rover',
            'Fiat',
            'Alfa Romeo',
            'Bentley',
            'Rolls-Royce',
            'Bugatti',
            'McLaren'
        ];

        foreach ($vehicleTypes as $type) {
            Car_type::query()->create([
                'name' => $type
            ]);
        }
    }
}
