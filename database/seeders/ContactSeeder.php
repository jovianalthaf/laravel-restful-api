<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('username', 'test')->first();
        Contact::create([
            'first_name' => 'test',
            'last_name' => 'test',
            'email' => 'test@gmail.com',
            'phone' => '11111',
            'user_id' => $user->id
        ]);

        // $user2 = User::where('username', 'test2')->first();
        // Contact::create([
        //     'first_name' => 'test2',
        //     'last_name' => 'test2',
        //     'email' => 'test@gmail.com2',
        //     'phone' => '111112',
        //     'user_id' => $user2->id
        // ]);
    }
}
