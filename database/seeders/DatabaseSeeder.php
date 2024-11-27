<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Other seeders can be called here
        $this->call([
            AccountTypesTableSeeder::class,
            // Add other seeders if necessary
        ]);
    }
}
