<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VerifikatorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $verifikators = [
            [
                'name' => 'CMD Verifikator',
                'email' => 'nisrinaauliyacareer@gmail.com',
                'username' => 'cmd',
                'dept_code' => 'CMD',
            ],
            [
                'name' => 'DINOV Verifikator',
                'email' => 'nisrinaauliya1@gmail.com',
                'username' => 'dinov',
                'dept_code' => 'DIN',
            ],
        ];

        foreach ($verifikators as $user) {

            $department = Department::where('code', $user['dept_code'])->first();

            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'username' => $user['username'],
                'dept_id' => $department->id,
                'role' => 'verifikator',
                'tim' => null,
                'password' => Hash::make('wahana123'),
            ]);
        }
    }
}
