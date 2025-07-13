<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
      $this->call(RoleSeeder::class);
      $this->call(AdminSeeder::class);
      $this->call(CountrySeeder::class);
      $this->call(Accommodation_typeSeeder::class);
      $this->call(ActivitySeeder::class);
      $this->call(Plane_typeSeeder::class);
      $this->call(Vehicle_typeSeeder::class);
      $this->call(Owner_CategorySeeder::class);
      $this->call(InfoSeeder::class);
    }
}
