<?php

namespace Layout\Layout;

use SimpleXMLElement;
use Symfony\Component\Finder\Finder;
use Illuminate\Contracts\Cache\Factory as Cache;

class Update
{
    /**
     * The cache instance.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Additional tag for cleaning layout cache convenience.
     */
    const LAYOUT_GENERAL_CACHE_TAG = 'LAYOUT_GENERAL_CACHE_TAG';

    /**
     * Layout Update Simplexml Element Class Name.
     *
     * @var string
     */
    protected $elementClass;

    /**
     * Cache key.
     *
     * @var string
     */
    protected $cacheId;

    /**
     * Cumulative array of update XML strings.
     *
     * @var array
     */
    protected $updates = [];
    /**
     * Handles used in this update.
     *
     * @var array
     */
    protected $handles = [];

    /**
     * @var Simplexml_Element
     */
    protected $moduleLayout;


    /**
     * Create a new view factory instance.
     *
     * @param \Illuminate\Contracts\Cache\Factory $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function getElementClass()
    {
        if (!$this->elementClass) {
            $this->elementClass = Element::class;
        }

        return $this->elementClass;
    }

    public function resetUpdates()
    {
        $this->updates = [];

        return $this;
    }

    public function addUpdate($update)
    {
        $this->updates[] = $update;

        return $this;
    }

    public function asArray()
    {
        return $this->updates;
    }

    public function asString()
    {
        return implode('', $this->updates);
    }

    public function resetHandles()
    {
        $this->handles = [];

        return $this;
    }

    public function addHandle($handle)
    {
        if (is_array($handle)) {
            foreach ($handle as $h) {
                $this->handles[$h] = 1;
            }
        } else {
            $this->handles[$handle] = 1;
        }

        return $this;
    }

    public function removeHandle($handle)
    {
        unset($this->handles[$handle]);

        return $this;
    }

    public function getHandles()
    {
        return array_keys($this->handles);
    }

    /**
     * Get cache id.
     *
     * @return string
     */
    public function getCacheId()
    {
        if (!$this->cacheId) {
            $this->cacheId = 'LAYOUT_'.md5(implode('__', $this->getHandles()));
        }

        return $this->cacheId;
    }

    public function loadCache()
    {
        if (!config('layout.cache.layout')) {
            return false;
        }

        if (!$result = $this->cache->get($this->getCacheId(), false)) {
            return false;
        }

        $this->addUpdate($result);

        return true;
    }

    public function saveCache()
    {
        if (!config('layout.cache.layout')) {
            return false;
        }

        $str = $this->asString();
        $tags = $this->getHandles();
        $tags[] = self::LAYOUT_GENERAL_CACHE_TAG;

        #TODO need to find neat solution
        if(!\Cache::getStore() instanceof TaggableStore) {
            return $this->cache->put($this->getCacheId(), $str, 0);
        } else {
            return $this->cache->tags($tags)->add($this->getCacheId(), $str, 0);
        }
    }

    /**
     * Load layout updates by handles.
     *
     * @param array|string $handles
     *
     * @return \Layout\Layout\Update
     */
    public function load($handles = [])
    {
        if (is_string($handles)) {
            $handles = [$handles];
        } elseif (!is_array($handles)) {
            throw new \Exception('Invalid layout update handle');
        }
        foreach ($handles as $handle) {
            $this->addHandle($handle);
        }

        if ($this->loadCache()) {
            return $this;
        }

        foreach ($this->getHandles() as $handle) {
            $this->merge($handle);
        }

        $this->saveCache();

        return $this;
    }

    public function asSimplexml()
    {
        $updates = trim($this->asString());
        $updates = '<'.'?xml version="1.0"?'.'><layout>'.$updates.'</layout>';

        return simplexml_load_string($updates, $this->getElementClass());
    }

    /**
     * Merge layout update by handle.
     *
     * @param string $handle
     *
     * @return \Layout\Layout\Update
     */
    public function merge($handle)
    {
        $packageUpdatesStatus = $this->fetchPackageLayoutUpdates($handle);

        return $this;
    }

    public function fetchPackageLayoutUpdates($handle)
    {
        $profilerKey = 'layout_update: '.$handle;
        start_profile($profilerKey);
        if (empty($this->moduleLayout)) {
            $this->fetchFileLayoutUpdates();
        }
        foreach ($this->moduleLayout->$handle as $updateXml) {
            #echo '<textarea style="width:600px; height:400px;">'.$handle.':'.print_r($updateXml,1).'</textarea>';

            /* @var Mage_Core_Model_Layout_Element $updateXml */

            $handle = $updateXml->getAttribute('ifhandle');
            if ($handle) {
                $handle = explode(' ', $handle);
                $handle = array_diff($handle, $this->getHandles());
                if (!empty($handle)) {
                    continue;
                }
            }

            $this->fetchRecursiveUpdates($updateXml);
            $this->addUpdate($updateXml->innerXml());
        }
        stop_profile($profilerKey);

        return true;
    }

    /**
     * Collect  layout updates.
     *
     * TODO need to plan as for of laravel theming
     *
     * @return \Layout\Layout\Update
     */
    public function fetchFileLayoutUpdates()
    {
        $elementClass = $this->getElementClass();
        $cacheKey = 'LAYOUT_'.'THEME_DEFAULT';
        $cacheTags = [self::LAYOUT_GENERAL_CACHE_TAG];

        if (config('layout.cache.layout') && ($layoutStr = $this->cache->get($cacheKey, false))) {
            $this->moduleLayout = simplexml_load_string($layoutStr, $elementClass);
        }

        if (empty($layoutStr)) {
            $this->moduleLayout = $this->getFileLayoutUpdatesXml();
            if (config('layout.cache.layout')) {
                if (config('cache.default') == 'file') {
                    $this->cache->put($cacheKey, $this->moduleLayout->asXml(), 0);
                } else {
                    $this->cache->tags($cacheTags)->put($cacheKey, $this->moduleLayout->asXml(), 0);
                }
            }
        }

        return $this;
    }

    public function fetchRecursiveUpdates($updateXml)
    {
        foreach ($updateXml->children() as $child) {
            if (strtolower($child->getName()) == 'update' && isset($child['handle'])) {
                $this->merge((string) $child['handle']);
                // Adding merged layout handle to the list of applied hanles
                $this->addHandle((string) $child['handle']);
            }
        }

        return $this;
    }

    /**
     * Collect and merge layout updates from file.
     *
     * @return \Layout\Layout\Element
     */
    public function getFileLayoutUpdatesXml()
    {
        $layoutXml = null;
        $elementClass = $this->getElementClass();

        $layoutStr = '';

        $fileLocationHandler = config('layout.handle_layout_section', function(){
            return 'default';
        });

        $fileLocation = config('layout.xml_location.'.$fileLocationHandler());
        
        if (!is_array($fileLocation)) {
            $fileLocation = [$fileLocation];
        }        

        foreach ($fileLocation as $location) {
            foreach (Finder::create()->files()->name('default.xml')->in($location) as $file) {
                $fileStr = $file->getContents();
                $fileXml = simplexml_load_string($fileStr, $elementClass);

                if (!$fileXml instanceof SimpleXMLElement) {
                    continue;
                }

                $layoutStr .= $fileXml->innerXml();
            }
            foreach (Finder::create()->files()->name('*.xml')->notName('default.xml')->notName('local.xml')->in($location) as $file) {
                $fileStr = $file->getContents();
                $fileXml = simplexml_load_string($fileStr, $elementClass);

                if (!$fileXml instanceof SimpleXMLElement) {
                    continue;
                }

                $layoutStr .= $fileXml->innerXml();
            }
            foreach (Finder::create()->files()->name('local.xml')->in($location) as $file) {
                $fileStr = $file->getContents();
                $fileXml = simplexml_load_string($fileStr, $elementClass);

                if (!$fileXml instanceof SimpleXMLElement) {
                    continue;
                }

                $layoutStr .= $fileXml->innerXml();
            }
        }

        $layoutXml = simplexml_load_string('<layouts>'.$layoutStr.'</layouts>', $elementClass);

        return $layoutXml;
    }
}
