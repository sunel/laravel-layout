<?php


return [

    /*
    |--------------------------------------------------------------------------
    |
    |--------------------------------------------------------------------------
    |
    |
    */
    'strict'            => false,
    'show_templat_hint' => true,
    'add_sample_menu'   => true,
    'session_name'      => 'laravel_session',

    'xml_location' => __DIR__.'/../layout',

    'cache' => [
        'fpc'    => false, //Danger Still Experimental
        'block'  => false,
        'layout' => false,
    ],

    'head' => [

        'title' => [
            'default' => 'Home',
            'prefix'  => '',
            'suffix'  => '',
        ],

        'media_type'   => 'text/html',
        'charset'      => 'utf-8',
        'description'  => 'Hello World',
        'keywords'     => 'Easy Layout for laravel based projects',
        'robots'       => 'INDEX,FOLLOW',
        'favicon_file' => '/favicon.ico',
        'includes'     => '',

    ],
    'cookienotice' => [

        'content' => 'Cookie notice content.',
        'noshow'  => true,
    ],

    'footer' => [

        'copyright' => 'Laravel is a trademark of Taylor Otwell. Copyright © Taylor Otwell.',
    ],

];
