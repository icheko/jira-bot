<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JIRA API Settings
    |--------------------------------------------------------------------------
    |
    | JIRA Bot uses the JIRA Rest API to process @mentions in comments
    |
    */
    'api_baseurl' => env('JIRA_BOT_API_BASEURL', 'http://jira.server/rest/api/2/'),
    'auth_username' => env('JIRA_BOT_AUTH_USERNAME', 'woof'),
    'auth_password' => env('JIRA_BOT_AUTH_PASSWORD', 'woof'),
];