<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JIRA API Settings
    |--------------------------------------------------------------------------
    |
    | Bot uses the JIRA Rest API to process @mentions in comments
    |
    */
    'jira' => [
        'api_baseurl' => env('BOT_JIRA_API_BASEURL', 'http://jira.server/rest/api/2/'),
        'auth_username' => env('BOT_JIRA_AUTH_USERNAME', 'woof'),
        'auth_password' => env('BOT_JIRA_AUTH_PASSWORD', 'woof'),
        'username' => env('BOT_JIRA_USERNAME', 'woof'), // for @mentions
    ],

    /*
    |--------------------------------------------------------------------------
    | Bamboo API Settings
    |--------------------------------------------------------------------------
    |
    | Bot uses the Bamboo Rest API to trigger builds, deployments, and report results
    |
    */
    'bamboo' => [
        'api_baseurl' => env('BOT_BAMBOO_API_BASEURL', 'http://beaver.server:8085/rest/api/'),
        'auth_username' => env('BOT_BAMBOO_AUTH_USERNAME', 'woof'),
        'auth_password' => env('BOT_BAMBOO_AUTH_PASSWORD', 'woof'),
        'username' => env('BOT_BAMBOO_USERNAME', 'woof'),
    ],
];