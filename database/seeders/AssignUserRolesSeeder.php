<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AssignUserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Assign 'super-admin' role to the first sample user
        $admin = User::where('email', 'admin@gmail.com')->first();
        if ($admin) {
            $admin->assignRole('super-admin');
        }

        // Assign 'staff' role to the rest of the sample users
        $staffEmails = [
            'juan-manalo@salon.com',
            'sofia-reyes@salon.com',
            'althea-dela-cruz@salon.com',
            'samantha-mendoza@salon.com',
            'anne-santos@salon.com',
            'julia-ramos@salon.com',
        ];
        foreach ($staffEmails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->assignRole('staff');
            }
        }
    }
}
