<?php

namespace Database\Seeders;

use Database\Factories\ProductFactory;
use Database\Factories\CategoryFactory;
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
        CategoryFactory::times(15)->create();
        ProductFactory::times(100)->create();
        $this->call(UserSeeder::class);
        $this->call(SettingsSeeder::class);
    }
}