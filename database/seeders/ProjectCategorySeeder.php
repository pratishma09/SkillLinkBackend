<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Web Development',
            'Mobile App Development',
            'Data Science',
            'UI/UX Design',
            'Digital Marketing',
            'Cybersecurity',
            'Cloud Computing',
            'DevOps',
            'AI & Machine Learning',
            'Blockchain Development',
        ];

        foreach ($categories as $name) {
            DB::table('project_categories')->insert([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
