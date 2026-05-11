<?php

namespace Database\Seeders;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itDept = Department::where('code', 'IT')->firstOrFail();

        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'Administrator',
                'email'    => 'admin@example.com',
                'username' => 'admin',
                'dept_id'  => $itDept->id,
                'role'     => 'admin',
                'tim'      => null,
                'password' => Hash::make('wahana123'),
            ]
        );
    }
}
