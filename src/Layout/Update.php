<?php namespace Ext\Layout;


class Update {
	
	
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
