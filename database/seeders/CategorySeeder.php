<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Category::count() > 0) {
            return; // Skip seeding if categories already exist
        }
        Category::create(['name' => 'Programming']);
        Category::create(['name' => 'UI UX Design']);
        Category::create(['name' => 'Cyber Security']);
    }
}
