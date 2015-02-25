<?php


return [

    /*
    |--------------------------------------------------------------------------
    |
    |--------------------------------------------------------------------------
    |
    |
    */
    'cache'             => false,
    'show_templat_hint' => true,
    'session_name'      => 'SID',

    'head' => [

        'title' => [
            'default' => 'Home',
            'prefix'  => '',
            'suffix'  => '',
        ],

        'includes'     => '',
        'media_type'   => 'text/html',
        'charset'      => 'utf-8',
        'description'  => 'Hello World',
        'keywords'     => 'Easy Layout for laravel based projects',
        'robots'       => 'INDEX,FOLLOW',
        'favicon_file' => '/favicon.ico',

    ],

    'mergeJS'  => false,
    'mergeCSS' => false,

    'footer' => [

        'copyright' => 'Laravel is a trademark of Taylor Otwell. Copyright © Taylor Otwell.',
    ],

];
