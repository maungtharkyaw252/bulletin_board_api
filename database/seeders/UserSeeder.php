<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Maung Thar Kyaw',
            'email' => 'mtm.maungtharkyaw@gmail.com',
            'password' => Hash::make("123456"),
            'type' => 0,
            'phone' => '09-963544995',
            'create_user_id' => 1,
            'updated_user_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}