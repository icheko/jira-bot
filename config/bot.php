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
        'base_url' => env('BOT_BAMBOO_BASEURL', 'http://bamboo.server:8085/'),
        'api_baseurl' => env('BOT_BAMBOO_API_BASEURL', 'http://bamboo.server:8085/rest/api/latest/'),
        'auth_username' => env('BOT_BAMBOO_AUTH_USERNAME', 'woof'),
        'auth_password' => env('BOT_BAMBOO_AUTH_PASSWORD', 'woof'),
        'username' => env('BOT_BAMBOO_USERNAME', 'woof'),
    ],

    /*
    |--------------------------------------------------------------------------
    | BitbucketServer API Settings
    |--------------------------------------------------------------------------
    |
    | Bot uses the Bitbucket Rest API to get branches and manage source code operations
    |
    */
    'bitbucket-server' => [
        'api_baseurl' => env('BOT_BITBUCKET_SERVER_API_BASEURL', 'http://bitbucket.server:7990/rest/api/1.0/'),
        'auth_username' => env('BOT_BITBUCKET_SERVER_AUTH_USERNAME', 'woof'),
        'auth_password' => env('BOT_BITBUCKET_SERVER_AUTH_PASSWORD', 'woof'),
        'username' => env('BOT_BITBUCKET_SERVER_USERNAME', 'woof'),
    ],
];