<?php


return [
    /*
    |--------------------------------------------------------------------------
    | Handle layout Route
    |--------------------------------------------------------------------------
    |
    | You can have your own way of adding layout handles
    |
    */

    'handle_layout_route'     => 'Layout\\Handle::route',

    /*
    |--------------------------------------------------------------------------
    | Handle layout Section
    |--------------------------------------------------------------------------
    |
    | You can have your own way of adding layout section
    |
    */

    'handle_layout_section'     => 'Layout\\Handle::section',

    /*
    |--------------------------------------------------------------------------
    | XML Location
    |--------------------------------------------------------------------------
    | 
    | List of absolute location path of the layout xml files.
    | 
    */

    'xml_location' => [
        'default' => [
            __DIR__.'/../layout',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | Enabling Strict mode will require all the router as Named routes,
    | if name are missing then Layout\Exceptions\InvalidRouterNameException
    | is thrown.
    |
    */
    
    'strict'            => false,
    
    /*
    |--------------------------------------------------------------------------
    | Template Hints
    |--------------------------------------------------------------------------
    |
    | Enabling template hint will allow you to visualize the class name 
    | and the template loaded for the block in the client side.This is
    | will let the developer easily look for the block which they need 
    | to debug.
    |
    */
    
    'show_template_hint' => false,
    
    /*
    |--------------------------------------------------------------------------
    | Sample Menus
    |--------------------------------------------------------------------------
    |
    | Enabling sample menu will add few top menu.This is just a hint to show
    | how a menu can be added. 
    |
    */
    
    'add_sample_menu'   => true,
    
    /*
    |--------------------------------------------------------------------------
    | Session Name
    |--------------------------------------------------------------------------
    | 
    | This variable is used to get the session details for caching purpose. 
    | Mostly this is will the same name given in the 'sesssion.cookie' config.
    |
    */
    
    'session_name'      => 'laravel_session',
    
    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    | Block
    |    Enabling block cache will cache the block HTML output with the given option.
    | Layout
    |   Enabling layout cache will cache the genrated xml files based on
    |   each handles (routers) and also avoid fetching from file every time.
    |
    */
    
    'cache' => [
        'block'  => false,
        'layout' => false,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Head Options
    |--------------------------------------------------------------------------
    | 
    | This section will be used to fill the basic option required in the <head>
    | section.
    |   
    | Title :- Use to set the page default title , prefix & suffix will added 
    |          to the title respectively.
    | Includes :- To add extra content in the head section. This can be 
    |             anything. 
    |
    */
    
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
    
    /*
    |--------------------------------------------------------------------------
    | Cookie Notice
    |--------------------------------------------------------------------------
    | This is to show the cookie notice section for a page.
    |
    */
    
    'cookienotice' => [

        'content' => 'Cookie notice content.',
        'noshow'  => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Footer 
    |--------------------------------------------------------------------------
    | To add the copyright content in the footer section 
    |
    */

    'footer' => [

        'copyright' => 'Laravel is a trademark of Taylor Otwell. Copyright © Taylor Otwell.',
    ],

];
