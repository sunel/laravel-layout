<?php

namespace Layout;

use Illuminate\Contracts\Events\Dispatcher;
use Layout\Exceptions\InvalidRouterNameException;
use Illuminate\Contracts\Cache\Factory as Cache;

class Factory
{
    const PROFILER_KEY = 'dispatch::route';
    /**
     * Additional tag for cleaning layout cache convenience.
     */
    const LAYOUT_GENERAL_CACHE_TAG = 'LAYOUT_GENERAL_FPC_CACHE_TAG';

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

     /**
     * The cache instance.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Title parts to be rendered in the page head title.
     *
     * @see self::title()
     *
     * @var array
     */
    protected $titles = [];

    /**
     * Array of breadcrumbs.
     * 
     * @see self::breadcrumbs()
     * 
     * @var array
     */
    protected $crumbs = [];

    /**
     * Options parts to be rendered in the page head.
     *
     * @var array
     */
    protected $headOptions = [];

    /**
     * Whether the default title should be removed.
     *
     * @see self::_title()
     *
     * @var bool
     */
    protected $removeDefaultTitle = false;

    /**
     * Create a new view factory instance.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     * @param \Illuminate\Contracts\Cache\Factory $cache
     */
    public function __construct(Dispatcher $events, Cache $cache)
    {
        $this->events = $events;
        $this->cache = $cache;
    }

    /**
     * Retrieve current layout object.
     *
     * @return \Layout\Layout
     */
    public function getLayout()
    {
        return app('render.layout');
    }

    /**
     * Get cache id.
     *
     * @return string
     */
    public function getCacheId()
    {
        return 'LAYOUT_FPC_'.md5(implode('__', $this->getLayout()->getUpdate()->getHandles()));
    }

    public function loadCache()
    {
        if (!config('layout.cache.fpc', false)) {
            return false;
        }

        if (!$result = $this->cache->get($this->getCacheId(), false)) {
            return false;
        }

        return $result;
    }

    public function saveCache($html)
    {
        if (!config('layout.cache.fpc', false)) {
            return false;
        }

        $tags = $this->getLayout()->getUpdate()->getHandles();
        $tags[] = self::LAYOUT_GENERAL_CACHE_TAG;

        #TODO need to find neat solution
        if (config('cache.default') == 'file') {
            return $this->cache->put($this->getCacheId(), $html, 0);
        } else {
            return $this->cache->tags($tags)->add($this->getCacheId(), $html, 0);
        }
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Illuminate\View\View
     */
    public function render($handles = null, $generateBlocks = true, $generateXml = true)
    {
        $this->loadHandles($handles);

        if (!$view = $this->loadCache()) {
            $this->loadLayout($generateBlocks, $generateXml);
            $view = $this->renderLayout();
            $this->saveCache($view);
        }

        return view('render::template.page.root', ['html' => $view]);
    }

    public function loadHandles($handles = null)
    {

        // if handles were specified in arguments load them first
        if (false !== $handles && '' !== $handles) {
            $this->getLayout()->getUpdate()->addHandle($handles ? $handles : 'default');
        }
        // add default layout handles for this action
        $this->addRouteLayoutHandles();
        $this->operatingSystemHandle();
        $this->browserHandle();
        $this->loadLayoutUpdates();

        return $this;
    }

    public function addRouteLayoutHandles()
    {
        $update = $this->getLayout()->getUpdate();
        // load action handle
        if (!empty($this->routeHandler())) {
            $update->addHandle($this->routeHandler());
        }

        return $this;
    }

    public function loadLayoutUpdates()
    {
        $profilerKey = self::PROFILER_KEY.'::'.$this->routeHandler();
        // dispatch event for adding handles to layout update
        $this->events->fire(
            'route.layout.load.before',
            ['route' => app('request'), 'layout' => $this->getLayout()]
        );
        // load layout updates by specified handles
        start_profile("$profilerKey::layout_load");
        $this->getLayout()->getUpdate()->load();
        stop_profile("$profilerKey::layout_load");

        return $this;
    }

    /**
     * Load layout by handles(s).
     *
     * @param string|null|bool $handles
     * @param bool             $generateBlocks
     * @param bool             $generateXml
     *
     * @return Layout\Factory
     */
    public function loadLayout($generateBlocks = true, $generateXml = true)
    {
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

    public function generateLayoutXml()
    {
        $profilerKey = self::PROFILER_KEY.'::'.$this->routeHandler();

        $this->events->fire(
            'route.layout.generate.xml.before',
            ['route' => app('request'), 'layout' => $this->getLayout()]
        );

        // generate xml from collected text updates
        start_profile("$profilerKey::layout_generate_xml");
        $this->getLayout()->generateXml();
        stop_profile("$profilerKey::layout_generate_xml");

        return $this;
    }

    public function generateLayoutBlocks()
    {
        $profilerKey = self::PROFILER_KEY.'::'.$this->routeHandler();
        // dispatch event for adding xml layout elements
        $this->events->fire(
            'route.layout.generate.blocks.before',
            ['route' => app('request'), 'layout' => $this->getLayout()]
        );

        // generate blocks from xml layout
        start_profile("$profilerKey::layout_generate_blocks");
        $this->getLayout()->generateBlocks();
        stop_profile("$profilerKey::layout_generate_blocks");

        $this->events->fire(
            'route.layout.generate.blocks.after',
            ['route' => app('request'), 'layout' => $this->getLayout()]
        );

        return $this;
    }

    /**
     * Rendering layout.
     *
     * @param string $output
     */
    public function renderLayout($output = '')
    {
        $profilerKey = self::PROFILER_KEY.'::'.$this->routeHandler();

        $this->_renderTitles();
        $this->_renderHeadOptions();
        $this->_renderBreadcrumbs();

        start_profile("$profilerKey::layout_render");
        if ('' !== $output) {
            $this->getLayout()->addOutputBlock($output);
        }

        $this->events->fire('route.layout.render.before');
        $this->events->fire('route.layout.render.before.'.$this->routeHandler());

        $output = $this->getLayout()->getOutput();

        stop_profile("$profilerKey::layout_render");

        return $output;
    }

    protected function routeHandler()
    {
        $routerHandler = config('layout.handle_layout', function(){});
        $routerHandler = $routerHandler();
        if(empty($routerHandler) || is_null($routerHandler)) {
            $route_name = \Route::currentRouteName();

            if (empty($route_name) && config('layout.strict', false)) {
                throw new InvalidRouterNameException('Invalid Router Name supplied');
            }

            $routerHandler = str_replace('.', '_', strtolower($route_name));
        }
        
        return $routerHandler;
        
    }

    /**
     * Add an extra title to the end or one from the end, or remove all.
     *
     * Usage examples:
     * $this->_title('foo')->_title('bar');
     * => bar / foo / <default title>
     *
     * $this->_title()->_title('foo')->_title('bar');
     * => bar / foo
     *
     * $this->_title('foo')->_title(false)->_title('bar');
     * bar / <default title>
     *
     * @see self::_renderTitles()
     *
     * @param string|false|-1|null $text
     * @param bool                 $resetIfExists
     *
     * @return Layout\Factory
     */
    public function title($text = null)
    {
        if (is_string($text)) {
            $this->titles[] = $text;
        } elseif (-1 === $text) {
            if (empty($this->titles)) {
                $this->removeDefaultTitle = true;
            } else {
                array_pop($this->titles);
            }
        }

        return $this;
    }
    
    /**
     * Prepare titles in the 'head' layout block
     * Supposed to work only in actions where layout is rendered
     * Falls back to the default logic if there are no titles eventually.
     *
     * @see self::loadLayout()
     * @see self::renderLayout()
     */
    protected function _renderTitles()
    {
        if ($this->_isLayoutLoaded && $this->titles) {
            $titleBlock = $this->getLayout()->getBlock('head');
            if ($titleBlock) {
                if (!$this->removeDefaultTitle) {
                    $title = trim($titleBlock->getTitle());
                    if ($title) {
                        array_unshift($this->titles, $title);
                    }
                }
                $titleBlock->setTitle(implode(' / ', array_reverse($this->titles)));
            }
        }
    }

    public function breadcrumbs($crumbs)
    {
        $this->crumbs = $crumbs;
    }

    protected function _renderBreadcrumbs()
    {
        $crumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($crumbs) {
            foreach ($this->crumbs as $name => $info) {
                $crumbs->addCrumb($name, $info);
            }
        }
    }

    public function setHeadOption($key, $value)
    {
        $this->headOptions[$key] = $value;
    }

    protected function _renderHeadOptions()
    {
        $titleBlock = $this->getLayout()->getBlock('head');
        if ($titleBlock) {
            foreach ($this->headOptions as $key => $value) {
                $titleBlock->setData($key, $value);
            }
        }
    }

    /**
     * Add a handle for operating systems, e.g.:
     * <layout>
     *   <operating_system_linux>
     *   </operating_system_linux>
     * </layout>.
     *
     * @return Layout\Factory
     */
    public function operatingSystemHandle()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/Linux/', $agent)) {
            $os = 'linux';
        } elseif (preg_match('/Win/', $agent)) {
            $os = 'windows';
        } elseif (preg_match('/Mac/', $agent)) {
            $os = 'osx';
        } else {
            $os = null;
        }
        if ($os) {
            $update = $this->getLayout()->getUpdate();
            $update->addHandle('operating_system_'.$os);
        }

        return $this;
    }
    /**
     * Add layout handle for browser type, e.g.:
     * <layout>
     *   <browser_firefox>
     *   </browser_firefox>
     * </layout>.
     *
     * @return Layout\Factory
     */
    public function browserHandle()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (stripos($agent, 'Firefox') !== false) {
            $agent = 'firefox';
        } elseif (stripos($agent, 'MSIE') !== false) {
            $agent = 'ie';
        } elseif (stripos($agent, 'iPad') !== false) {
            $agent = 'ipad';
        } elseif (stripos($agent, 'Android') !== false) {
            $agent = 'android';
        } elseif (stripos($agent, 'Chrome') !== false) {
            $agent = 'chrome';
        } elseif (stripos($agent, 'Safari') !== false) {
            $agent = 'safari';
        } else {
            $agent = null;
        }
        if ($agent) {
            $update = $this->getLayout()->getUpdate();
            $update->addHandle('browser_'.$agent);
        }

        return $this;
    }
}
