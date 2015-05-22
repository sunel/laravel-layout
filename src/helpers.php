<?php

if (!function_exists('getlayout')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Layout\Factory
     */
    function getlayout($handles = null, $generateBlocks = true, $generateXml = true)
    {
        $factory = app('render');

        return $factory->loadHandles($handles)->loadLayout($generateBlocks, $generateXml);
    }
}

if (!function_exists('render')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Illuminate\View\View
     */
    function render($handles = null, $generateBlocks = true, $generateXml = true)
    {
        $factory = app('render');

        return $factory->render($handles, $generateBlocks, $generateXml);
    }
}

if (!function_exists('start_profile')) {
    /**
     * Start the profile for debugging.
     *
     * @param string $name
     */
    function start_profile($name)
    {
        if (config('debugbar.enabled', false)) {
            Debugbar::startMeasure($name);
        }
    }
}

if (!function_exists('stop_profile')) {
    /**
     * Stop the profile for debugging.
     *
     * @param string $name
     */
    function stop_profile($name)
    {
        if (config('debugbar.enabled', false)) {
            Debugbar::stopMeasure($name);
        }
    }
}
