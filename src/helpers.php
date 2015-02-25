<?php

if (! function_exists('render')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Illuminate\View\View
     */
    function render()
    {
        $factory = app('render');

        return $factory->render();
    }
}

if (! function_exists('start_profile')) {
    /**
     * Start the profile for debugging.
     *
     * @param string $name
     *
     * @return void
     */
    function start_profile($name)
    {
        if (config('debugbar.enabled', false)) {
            Debugbar::startMeasure($name);
        }
    }
}

if (! function_exists('stop_profile')) {
    /**
     * Stop the profile for debugging.
     *
     * @param string $name
     *
     * @return void
     */
    function stop_profile($name)
    {
        if (config('debugbar.enabled', false)) {
            Debugbar::stopMeasure($name);
        }
    }
}
