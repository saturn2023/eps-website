<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Local admin login. Change the password after first login on any shared server.
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@eps.test')],
            ['name' => 'EPS Admin', 'password' => Hash::make(env('ADMIN_PASSWORD', 'password'))],
        );

        $this->call([ContentSeeder::class, AboutPagesSeeder::class, SitePagesSeeder::class]);

        // List every seeded image and file in the Media Library.
        \App\Models\Media::sync();
    }
}
