<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->create([
          'name' => 'admin',
          'email' => 'admin@gmail.com',
          'password' => '123123',
          'phone_number' => '0937523553',
          'status' => '0',
          'role_id' => '1',
        ]);
    }
}
