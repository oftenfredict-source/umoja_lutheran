<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = [
            [
                'name' => 'Bar Department',
                'code' => 'bar',
                'description' => 'Handles all beverages, spirits, wines, and bar supplies.',
                'is_active' => true,
            ],
            [
                'name' => 'Kitchen Department',
                'code' => 'kitchen',
                'description' => 'Handles all food processing, ingredients, and kitchen supplies.',
                'is_active' => true,
            ],
            [
                'name' => 'Housekeeping Department',
                'code' => 'housekeeping',
                'description' => 'Handles guest room supplies, cleaning equipment, and linens.',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                $dept
            );
        }
    }
}
