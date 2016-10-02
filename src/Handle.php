<?php

namespace Layout;

class Handle
{
    public static function route()
    {
        $routeName = \Route::currentRouteName();
        $routerHandler = str_replace('.', '_', strtolower($routeName));
        return $routerHandler;
    }

    public static function section()
    {
        if (\Request::is(config('backend.prefix', 'admin').'/*')) {
            return 'admin';
        }
        return 'front';
    }
}
