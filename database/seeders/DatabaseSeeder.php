<?php

namespace Database\Seeders;

use Database\Factories\ProductFactory;
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
        ProductFactory::times(100)->create();
        $this->call(UserSeeder::class);
        $this->call(SettingsSeeder::class);
    }
}
