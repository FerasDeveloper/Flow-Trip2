<?php

namespace Database\Seeders;

use App\Models\Plan_type;
use App\Models\Plane;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Plane_typeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planes = [
            'Airbus A320',
            'Airbus A330',
            'Airbus A350',
            'Airbus A380',
            'Boeing 737',
            'Boeing 747',
            'Boeing 757',
            'Boeing 767',
            'Boeing 777',
            'Boeing 787',
            'Embraer E170',
            'Embraer E190',
            'Bombardier CRJ900',
            'Bombardier Q400',
            'ATR 72',
            'Cessna 172',
            'Cessna 208',
            'Pilatus PC-12',
            'Gulfstream G650',
            'Dassault Falcon 7X',
            'Learjet 75',
            'Cirrus SR22',
            'Piper PA-28',
            'Beechcraft King Air',
            'Diamond DA40',
            'Mooney M20',
            'Socata TB20',
            'Robin DR400',
            'Extra EA-300',
            'Aeroprakt A-22'
        ];

        foreach ($planes as $plane) {
            Plan_type::query()->create([
                'name' => $plane
            ]);
        }
    }
} 