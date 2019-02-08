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
            'command_name' => 'skip-tests',
            'alias' => 'st',
        ]);

        CommandType::updateOrCreate([
            'command_name' => 'deploy',
        ]);
    }
}
