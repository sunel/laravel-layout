<?php


return [

    /*
    |--------------------------------------------------------------------------
    |
    |--------------------------------------------------------------------------
    |
    |
    */
    'strict' 			=> false,
    'show_templat_hint' => false,
    'session_name'      => 'SID',

    'cache' => [
        'block'  => false,
        'layout' => false,
    ],

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
    'cookienotice' => [
    	
    	'content' => 'Cookie notice content.',
		'noshow' => true,
    ],

    'mergeJS'  => false,
    'mergeCSS' => false,

    'footer' => [

        'copyright' => 'Laravel is a trademark of Taylor Otwell. Copyright © Taylor Otwell.',
    ],

];
