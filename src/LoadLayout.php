<?php namespace Ext;

use Ext\Repository;
use Symfony\Component\Finder\Finder;
use Illuminate\Contracts\Foundation\Application;
use Ext\Contracts\Render\Repository as RepositoryContract;

class LoadLayout {

	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return \Ext\Repository
	 */
	public function bootstrap(Application $app)
	{	
		$items = [];

		// First we will see if we have a cache configuration file. If we do, we'll load
		// the configuration items from that file so that it is very quick. Otherwise
		// we will need to spin through every configuration file and load them all.
		if (file_exists($cached = storage_path('layout.php')))
		{
			$items = require $cached;

			$loadedFromCache = true;
		}

		$layout = new Repository($items);

		// Next we will spin through all of the configuration files in the configuration
		// directory and load each one into the repository. This will make all of the
		// options available to the developer for use in various parts of this app.
		if ( ! isset($loadedFromCache))
		{
			$this->loadLayoutFiles($app, $layout);
		}

		return $layout;

	}

	/**
	 * Load the configuration items from all of the files.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @param  \Ext\Contracts\Render\Repository  $layout
	 * @return void
	 */
	protected function loadLayoutFiles(Application $app, RepositoryContract $layout)
	{	
		$nodes = [];
		foreach ($this->getLayoutFiles($app) as $key => $path)
		{
			qp($path)->children()->each(function($index,$node) use(&$nodes) {
				
				$nodes[$node->nodeName][] = $node;

			});	
		}
		$layout->set($nodes);
	}

	/**
	 * Get all of the configuration files for the application.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return array
	 */
	protected function getLayoutFiles(Application $app)
	{
		$files = [];

		foreach (Finder::create()->files()->name('*.xml')->in(__DIR__.'/../views/layout') as $file)
		{
			$files[basename($file->getRealPath(), '.xml')] = $file->getRealPath();
		}

		return $files;
	}

}
