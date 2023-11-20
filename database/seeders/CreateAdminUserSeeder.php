<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;


class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name' => 'kms',
            'email' => 'kt@gmail.com',
            'password' => bcrypt('123456')
        ]);

        $userRole = \App\Models\Role::where('name', 'admin')->first();
        $user->roles()->attach($userRole);
    }

}
