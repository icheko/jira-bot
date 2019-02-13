<?php

use Illuminate\Database\Seeder;
use App\Models\CommandType;

class CommandTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CommandType::updateOrCreate([
            'command' => 'skip-tests',
            'alias' => 'st',
        ]);

        CommandType::updateOrCreate([
            'command' => 'deploy',
        ]);
    }
}
