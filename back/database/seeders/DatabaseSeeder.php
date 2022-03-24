<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(
            cancellation_reason::class,
        );
        $this->call(
            status::class,
        );
        $this->call(
            move_type::class,
        );
        $this->call(
            UserSeeder::class
        );
    }
}
