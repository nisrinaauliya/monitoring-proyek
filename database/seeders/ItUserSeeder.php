<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ItUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $itDepartment = Department::where('code', 'IT')->first();

        $users = [

            // PROJECT LEADER
            [
                'name' => 'PL Internal',
                'email' => 'nisrinaauliyacareer@gmail.com',
                'username' => 'plinternal',
                'role' => 'project_leader',
                'tim' => 'internal',
            ],
            [
                'name' => 'PL Eksternal',
                'email' => 'bbypeach10@gmail.com',
                'username' => 'pleksternal',
                'role' => 'project_leader',
                'tim' => 'eksternal',
            ],

            // BUSINESS ANALYST INTERNAL
            [
                'name' => 'BA Internal 1',
                'email' => 'abnewab6ix522@gmail.com',
                'username' => 'bainternal1',
                'role' => 'business_analyst',
                'tim' => 'internal',
            ],
            [
                'name' => 'BA Internal 2',
                'email' => 'woojinlatte211@gmail.com',
                'username' => 'bainternal2',
                'role' => 'business_analyst',
                'tim' => 'internal',
            ],
            [
                'name' => 'BA Internal 3',
                'email' => 'bainternal3@example.com',
                'username' => 'bainternal3',
                'role' => 'business_analyst',
                'tim' => 'internal',
            ],

            // BUSINESS ANALYST EKSTERNAL
            [
                'name' => 'BA Eksternal 1',
                'email' => 'spektrumdelapan@gmail.com',
                'username' => 'baeksternal1',
                'role' => 'business_analyst',
                'tim' => 'eksternal',
            ],
            [
                'name' => 'BA Eksternal 2',
                'email' => 'nisrinaauliya1@gmail.com',
                'username' => 'baeksternal2',
                'role' => 'business_analyst',
                'tim' => 'eksternal',
            ],
            [
                'name' => 'BA Eksternal 3',
                'email' => 'baeksternal3@example.com',
                'username' => 'baeksternal3',
                'role' => 'business_analyst',
                'tim' => 'eksternal',
            ],

            // DEVELOPER INTERNAL
            [
                'name' => 'Developer Internal 1',
                'email' => 'devinternal1@example.com',
                'username' => 'devinternal1',
                'role' => 'developer',
                'tim' => 'internal',
            ],
            [
                'name' => 'Developer Internal 2',
                'email' => 'devinternal2@example.com',
                'username' => 'devinternal2',
                'role' => 'developer',
                'tim' => 'internal',
            ],
            [
                'name' => 'Developer Internal 3',
                'email' => 'devinternal3@example.com',
                'username' => 'devinternal3',
                'role' => 'developer',
                'tim' => 'internal',
            ],
            [
                'name' => 'Developer Internal 4',
                'email' => 'devinternal4@example.com',
                'username' => 'devinternal4',
                'role' => 'developer',
                'tim' => 'internal',
            ],

            // DEVELOPER EKSTERNAL
            [
                'name' => 'Developer Eksternal 1',
                'email' => 'deveksternal1@example.com',
                'username' => 'deveksternal1',
                'role' => 'developer',
                'tim' => 'eksternal',
            ],
            [
                'name' => 'Developer Eksternal 2',
                'email' => 'deveksternal2@example.com',
                'username' => 'deveksternal2',
                'role' => 'developer',
                'tim' => 'eksternal',
            ],
            [
                'name' => 'Developer Eksternal 3',
                'email' => 'deveksternal3@example.com',
                'username' => 'deveksternal3',
                'role' => 'developer',
                'tim' => 'eksternal',
            ],
            [
                'name' => 'Developer Eksternal 4',
                'email' => 'deveksternal4@example.com',
                'username' => 'deveksternal4',
                'role' => 'developer',
                'tim' => 'eksternal',
            ],
        ];

        foreach ($users as $user) {

            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'username' => $user['username'],
                'dept_id' => $itDepartment->id,
                'role' => $user['role'],
                'tim' => $user['tim'],
                'password' => Hash::make('wahana123'),
            ]);
        }
    }
}
