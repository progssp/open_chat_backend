<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GroupDetail;
use App\Models\User;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            "user_first_name" => "Jennifer",
            "user_last_name" => "Walsh",
            "email" => "jennifer@openchat.com",
            "username" => "jwlssp",
            "password" =>
                '$2a$12$JUx1b3crprfdJwF2eRsRhO7XjvUj/poodayp8nuCIzTMSnIlsR4ii', //for sample
            "icon" => "/user_images/profile_pictures/picture.png",
            "present_status" => "online",
            "last_online" => now(),
            "email_verified_at" => now(),
            "remember_token" => Str::random(10),
        ]);
        User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
