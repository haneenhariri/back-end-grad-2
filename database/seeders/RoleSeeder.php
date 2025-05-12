<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::create(['name' => 'admin']);
        $user1 = User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => 123123123
        ]);
        $user1->assignRole($admin);

        //----------------
        $instructor = Role::create(['name' => 'instructor']);
        $user2 = User::create([
            'name' => 'instructor',
            'email' => 'instructor@gmail.com',
            'password' => 123123123
        ]);
        Instructor::create([
            'user_id'=>$user2->id,
            'education'=>'education',
            'specialization'=>'specialization',
            'summery'=>'summery',
        ]);
        $user2->assignRole($instructor);

        //---------------------
        $student = Role::create(['name' => 'student']);
        $user3 = User::create([
            'name' => 'student',
            'email' => 'student@gmail.com',
            'password' => 123123123
        ]);
        $user3->assignRole($student);


    }
}
