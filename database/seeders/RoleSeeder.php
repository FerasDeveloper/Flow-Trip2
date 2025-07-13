<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_name' => 'admin'],
            ['role_name' => 'sub_admin'],
            ['role_name' => 'user'],
            ['role_name' => 'owner'],
        ];

        foreach ($roles as $role) {
            Role::query()->create($role);
        }
    }
}
