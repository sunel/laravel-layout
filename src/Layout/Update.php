<?php namespace Ext\Layout;

use Ext\Layout\Element;

class Update {
	
	/**
     * Cumulative array of update XML strings
     *
     * @var array
     */
    protected $_updates = array();
    /**
     * Handles used in this update
     *
     * @var array
     */
    protected $_handles = array();



    public function getElementClass()
    {
        if (!$this->_elementClass) {
            $this->_elementClass = Element::class;
        }
        return $this->_elementClass;
    }

    public function resetUpdates()
    {
        $this->_updates = array();
        return $this;
    }

    public function addUpdate($update)
    {
        $this->_updates[] = $update;
        return $this;
    }

    public function asArray()
    {
        return $this->_updates;
    }

    public function asString()
    {
        return implode('', $this->_updates);
    }

    public function resetHandles()
    {
        $this->_handles = array();
        return $this;
    }

    public function addHandle($handle)
    {
        if (is_array($handle)) {
            foreach ($handle as $h) {
                $this->_handles[$h] = 1;
            }
        } else {
            $this->_handles[$handle] = 1;
        }
        return $this;
    }

    public function removeHandle($handle)
    {
        unset($this->_handles[$handle]);
        return $this;
    }

    public function getHandles()
    {
        return array_keys($this->_handles);
    }


     /**
     * Load layout updates by handles
     *
     * @param array|string $handles
     * @return \Ext\Layout\Update
     */
    public function load($handles=array())
    {
        if (is_string($handles)) {
            $handles = array($handles);
        } elseif (!is_array($handles)) {
            throw new Exception('Invalid layout update handle');
        }
        foreach ($handles as $handle) {
            $this->addHandle($handle);
        }

        /*if ($this->loadCache()) {
            return $this;
        }*/
        
        foreach ($this->getHandles() as $handle) {
            $this->merge($handle);
        }

        //$this->saveCache();
        return $this;
    }

    public function asSimplexml()
    {
        $updates = trim($this->asString());
        $updates = '<'.'?xml version="1.0"?'.'><layout>'.$updates.'</layout>';
        return simplexml_load_string($updates, $this->getElementClass());
    }

    /**
     * Merge layout update by handle
     *
     * @param string $handle
     * @return Mage_Core_Model_Layout_Update
     */
    public function merge($handle)
    {
        $packageUpdatesStatus = $this->fetchPackageLayoutUpdates($handle);
        
        return $this;
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
