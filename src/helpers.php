<?php

if (! function_exists('render')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Illuminate\View\View
     */
    function render()
    {
        $factory = app('render');

        return $factory->render();
    }
}
