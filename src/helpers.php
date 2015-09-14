<?php

if (!function_exists('loadLayout')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Layout\Factory
     */
    function loadLayout($handles = null, $generateBlocks = true, $generateXml = true)
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

if (!function_exists('renderWithOptions')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Illuminate\View\View
     */
    function renderWithOptions(array $options, $handles = null, $generateBlocks = true, $generateXml = true)
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
                default:
                    $factory->setHeadOption($key, $option);
                    break;
            }
        }

        $factory->loadHandles($handles)->loadLayout($generateBlocks, $generateXml);

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
