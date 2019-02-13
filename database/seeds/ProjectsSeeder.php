<?php

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Project::updateOrCreate([
            'jira_key' => 'POR',
            'bamboo_key' => 'POR-POUI',
            'bitbucket_repo' => 'STARS20/repos/master',
        ]);

        Project::updateOrCreate([
            'jira_key' => 'UNK',
            'bamboo_key' => null,
            'bitbucket_repo' => null,
        ]);
    }
}
