<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'code' => 'MKT',
                'name' => 'Marketing',
            ],
            [
                'code' => 'TSD',
                'name' => 'Technical Service Development',
            ],
            [
                'code' => 'PMD',
                'name' => 'Part Main Dealer',
            ],
            [
                'code' => 'HC3',
                'name' => 'Honda Customer Care Center',
            ],
            [
                'code' => 'HCQD',
                'name' => 'Human Capital Quality Development',
            ],
            [
                'code' => 'MPA',
                'name' => 'MPA',
            ],
            [
                'code' => 'WARI',
                'name' => 'Wahanaartha Ritelindo',
            ],
            [
                'code' => 'DIN',
                'name' => 'Digital Initiatives',
            ],
            [
                'code' => 'CMD',
                'name' => 'Corporate Management Development',
            ],
            [
                'code' => 'IT',
                'name' => 'Information Technology',
            ],
            [
                'code' => 'GA',
                'name' => 'General Affair',
            ],
            [
                'code' => 'TAX',
                'name' => 'Taxation',
            ],
            [
                'code' => 'ACC',
                'name' => 'Accounting',
            ],
            [
                'code' => 'BC',
                'name' => 'Budget Control',
            ],
            [
                'code' => 'FIN',
                'name' => 'Finance',
            ],
            [
                'code' => 'COMP',
                'name' => 'Compliance',
            ],
            [
                'code' => 'HRD',
                'name' => 'Human Resources Development',
            ],
            [
                'code' => 'LEGAL',
                'name' => 'Legal',
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    
    }
}
