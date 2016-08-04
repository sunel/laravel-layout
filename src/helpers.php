<?php

if (!function_exists('loadLayout')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Layout\Factory
     */
    function loadLayout($handles = null, $generateBlocks = true, $generateXml = true, $disableRouteHandle = false)
    {
        $factory = app('render');

        return $factory->loadHandles($handles, $disableRouteHandle)->loadLayout($generateBlocks, $generateXml);
    }
}

if (!function_exists('render')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Illuminate\View\View
     */
    function render($handles = null, $generateBlocks = true, $generateXml = true, $disableRouteHandle = false)
    {
        $factory = app('render');

        return $factory->render($handles, $generateBlocks, $generateXml, $disableRouteHandle);
    }
}

if (!function_exists('renderWithOptions')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Illuminate\View\View
     */
    function renderWithOptions(array $options, $handles = null, $generateBlocks = true, $generateXml = true, $disableRouteHandle = false)
    {
        $factory = app('render');

        foreach ($options as $key => $option) {
            switch ($key) {
                case 'titles':
                    foreach ($option as $title) {
                        $factory->title($title);
                    }
                    break;
                case 'breadcrumbs':
                    $factory->breadcrumbs($option);
                    break;
                case 'with':
                    foreach ($option as $key => $value) {
                        view()->share($key, $value);
                    }
                   break; 
                case 'layout_handles':
                    foreach ($option as $value) {
                        $factory->addCustomHandle($value);
                    }
                   break;    
                default:
                    $factory->setHeadOption($key, $option);
                    break;
            }
        }

        $factory->loadHandles($handles, $disableRouteHandle)->loadLayout($generateBlocks, $generateXml);

        return $factory->renderLayout();
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
