<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Role::count() > 0) {
            return; // Skip seeding if roles already exist
        }
        $adminRole = Role::create([
            'name' => 'admin',
        ]);

        $studentRole = Role::create([
            'name' => 'student',
        ]);

        $mentorRole = Role::create([
            'name' => 'mentor',
        ]);

        $user = User::create(
            [
                'name' => 'Obito',
                'email' => 'obito@team.com',
                'password' => bcrypt('123123123')
            ]
        );

        $user->assignRole($adminRole);
    }
}
