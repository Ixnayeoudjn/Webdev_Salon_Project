<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $users = [
            ['name'=>'Severino Bedis','email'=>'admin@gmail.com','password'=>Hash::make('IamAdmin123'),'role'=>'super-admin'],
            ['name'=>'Juan Manalo','email'=>'juan-manalo@salon.com','password'=>Hash::make('password'),'role'=>'staff'],
            ['name'=>'Sofia Reyes','email'=>'sofia-reyes@salon.com','password'=>Hash::make('password'),'role'=>'staff'],
            ['name'=>'Althea Dela Cruz','email'=>'althea-dela-cruz@salon.com','password'=>Hash::make('password'),'role'=>'staff'],
            ['name'=>'Samantha Mendoza','email'=>'samantha-mendoza@salon.com','password'=>Hash::make('password'),'role'=>'staff'],
            ['name'=>'Anne Santos','email'=>'anne-santos@salon.com','password'=>Hash::make('password'),'role'=>'staff'],
            ['name'=>'Julia Ramos','email'=>'julia-ramos@salon.com','password'=>Hash::make('password'),'role'=>'staff'],
        ];
        foreach ($users as $userData) {
            $user = User::create([
                'name'=>$userData['name'],
                'email'=>$userData['email'],
                'password'=>$userData['password'],
            ]);
            $user->assignRole($userData['role']);
        }
    }
}
