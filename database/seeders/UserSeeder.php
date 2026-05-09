<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'User Marketing 1',
                'email' => 'bbypeach10@gmail.com',
                'username' => 'usermarketing1',
                'dept_code' => 'MKT',
            ],
            [
                'name' => 'User Marketing 2',
                'email' => 'nisrinaauliya2@gmail.com',
                'username' => 'usermarketing2',
                'dept_code' => 'MKT',
            ],
            [
                'name' => 'User Finance 1',
                'email' => 'woojinlatte211@gmail.com',
                'username' => 'userfinance1',
                'dept_code' => 'FIN',
            ],
            [
                'name' => 'User Finance 2',
                'email' => 'niseurina1001@gmail.com',
                'username' => 'userfinance2',
                'dept_code' => 'FIN',
            ],
            [
                'name' => 'User HRD 1',
                'email' => 'abnewab6ix522@gmail.com',
                'username' => 'userhrd1',
                'dept_code' => 'HRD',
            ],
            [
                'name' => 'User CMD 1',
                'email' => 'niseurina1001@gmail.com',
                'username' => 'usercmd1',
                'dept_code' => 'CMD',
            ],
            [
                'name' => 'User DINOV 1',
                'email' => 'spektrumdelapan@gmail.com',
                'username' => 'userdinov1',
                'dept_code' => 'DIN',
            ],
        ];

        foreach ($users as $user) {

            $department = Department::where('code', $user['dept_code'])->first();

            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'username' => $user['username'],
                'dept_id' => $department->id,
                'role' => 'user',
                'tim' => null,
                'password' => Hash::make('wahana123'),
            ]);
        }
    }
}
