<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('demo.admin_email');
        $password = config('demo.admin_password');

        if (! $email || ! $password) {
            $this->command?->warn('Demo user not created: configure DEMO_ADMIN_EMAIL and DEMO_ADMIN_PASSWORD locally.');

            return;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => 'Demo Admin', 'password' => $password]
        );
    }
}
