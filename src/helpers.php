<?php

if ( ! function_exists('render'))
{
	/**
	 * Get the evaluated view contents for the given view.
	 *
	 * @param  string  $view
	 * @param  array   $data
	 * @param  array   $mergeData
	 * @return \Illuminate\View\View
	 */
	function render($view = null, $data = array(), $mergeData = array())
	{
		$factory = app('render');
		if (func_num_args() === 0)
		{
			return $factory;
		}
		return $factory->make($view, $data, $mergeData);
	}
}
