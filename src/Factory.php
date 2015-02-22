<?php namespace Ext;

use Debugbar;
use Illuminate\Contracts\Events\Dispatcher;

class Factory {
	
	const PROFILER_KEY   = 'dispatch::route';
	 
	/**
     * Blocks registry
     *
     * @var array
     */
    protected $_blocks = array();
	
    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Create a new view factory instance.
     * 
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

	
	/**
     * Retrieve current layout object
     *
     * @return Mage_Core_Model_Layout
     */
    public function getLayout()
    {
        return app('render.layout');
    }

	/**
	 * Get the evaluated view contents for the given view.
	 *
	 * @param  string  $view
	 * @param  array   $data
	 * @param  array   $mergeData
	 * @return \Illuminate\View\View
	 */
	public function render($handles = null, $generateBlocks = true, $generateXml = true)
	{
		
		$this->loadLayout($handles,$generateBlocks,$generateXml);
		
		$view = $this->renderLayout();
		
        return view('render::page.root',['html'=>$view]);
	}
	
	
    /**
     * Load layout by handles(s)
     *
     * @param   string|null|bool $handles
     * @param   bool $generateBlocks
     * @param   bool $generateXml
     * @return  Mage_Core_Controller_Varien_Action
     */
    public function loadLayout($handles = null, $generateBlocks = true, $generateXml = true)
    {
        // if handles were specified in arguments load them first
        if (false!==$handles && ''!==$handles) {
            $this->getLayout()->getUpdate()->addHandle($handles ? $handles : 'default');
        }
        // add default layout handles for this action
        $this->addRouteLayoutHandles();
        $this->loadLayoutUpdates();
        if (!$generateXml) {
            return $this;
        }
        $this->generateLayoutXml();
        if (!$generateBlocks) {
            return $this;
        }
        $this->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        return $this;
    }
	
    public function addRouteLayoutHandles()
    {
        $update = $this->getLayout()->getUpdate();
        
        $update->addHandle('XXXX__YYYY');
        
        // load action handle
        $update->addHandle($this->routeHandler());
		
        return $this;
    }
	
    public function loadLayoutUpdates()
    {
        $_profilerKey = self::PROFILER_KEY . '::' .$this->routeHandler();
        // dispatch event for adding handles to layout update
        $this->events->fire(
            'route.layout.load.before',
            array('route'=>$this, 'layout'=>$this->getLayout())
        );
        // load layout updates by specified handles
        Debugbar::startMeasure("$_profilerKey::layout_load");
        $this->getLayout()->getUpdate()->load();
        Debugbar::stopMeasure("$_profilerKey::layout_load");
        return $this;
    }
	
    public function generateLayoutXml()
    {
        $_profilerKey = self::PROFILER_KEY . '::' . $this->routeHandler();
        
        $this->events->fire(
            'route.layout.generate.xml.before',
            array('route'=>$this, 'layout'=>$this->getLayout())
        );
        
        // generate xml from collected text updates
        Debugbar::startMeasure("$_profilerKey::layout_generate_xml");
        $this->getLayout()->generateXml();
        Debugbar::stopMeasure("$_profilerKey::layout_generate_xml");
        
        return $this;
    }
	
    public function generateLayoutBlocks()
    {
        $_profilerKey = self::PROFILER_KEY . '::' . $this->routeHandler();
        // dispatch event for adding xml layout elements
        
        $this->events->fire(
            'route.layout.generate.blocks.before',
            array('route'=>$this, 'layout'=>$this->getLayout())
        );
            
        // generate blocks from xml layout
        Debugbar::startMeasure("$_profilerKey::layout_generate_blocks");
        $this->getLayout()->generateBlocks();
        Debugbar::stopMeasure("$_profilerKey::layout_generate_blocks");
        
        
        $this->events->fire(
            'route.layout.generate.blocks.after',
            array('route'=>$this, 'layout'=>$this->getLayout())
        );
		
        return $this;
    }
    
    /**
     * Rendering layout
     *
     * @param   string $output
     */
    public function renderLayout($output='')
    {
        $_profilerKey = self::PROFILER_KEY . '::' . $this->routeHandler();
        
        Debugbar::startMeasure("$_profilerKey::layout_render");
        if (''!==$output) {
            $this->getLayout()->addOutputBlock($output);
        }
		
        $this->events->fire('route.layout.render.before');
        $this->events->fire('route.layout.render.before.'.$this->routeHandler());
            
        $this->getLayout()->setDirectOutput(false);

        $output = $this->getLayout()->getOutput();
        
       
        Debugbar::stopMeasure("$_profilerKey::layout_render");
		
        return $output;
    }
	

	protected function routeHandler()
	{
		$route_name = \Route::currentRouteName();
		
		if(empty($route_name)){
					
			throw new InvalidRouterNameException('Invalid Router Name supplied');
		}	
		return str_replace('.','_',strtolower($route_name));
	}

	
}
