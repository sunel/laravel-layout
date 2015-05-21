<?php namespace Layout\Layout;

use Cache;
use SimpleXMLElement;
use Symfony\Component\Finder\Finder;

class Update
{
    /**
     * Additional tag for cleaning layout cache convenience.
     */
    const LAYOUT_GENERAL_CACHE_TAG = 'LAYOUT_GENERAL_CACHE_TAG';

    /**
     * Layout Update Simplexml Element Class Name.
     *
     * @var string
     */
    protected $_elementClass;

    /**
     * Cache key.
     *
     * @var string
     */
    protected $_cacheId;

    /**
     * Cumulative array of update XML strings.
     *
     * @var array
     */
    protected $_updates = [];
    /**
     * Handles used in this update.
     *
     * @var array
     */
    protected $_handles = [];

    /**
     * @var Simplexml_Element
     */
    protected $_moduleLayout;

    public function getElementClass()
    {
        if (!$this->_elementClass) {
            $this->_elementClass = Element::class;
        }

        return $this->_elementClass;
    }

    public function resetUpdates()
    {
        $this->_updates = [];

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
        $this->_handles = [];

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
     * Get cache id.
     *
     * @return string
     */
    public function getCacheId()
    {
        if (!$this->_cacheId) {
            $this->_cacheId = 'LAYOUT_'.md5(implode('__', $this->getHandles()));
        }

        return $this->_cacheId;
    }

    public function loadCache()
    {
        if (!config('layout.cache.layout')) {
            return false;
        }

        if (!$result = Cache::get($this->getCacheId(), false)) {
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
        if (config('cache.default') == 'file') {
            return Cache::put($this->getCacheId(), $str, 0);
        } else {
            return Cache::tags($tags)->add($this->getCacheId(), $str, 0);
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
        $_profilerKey = 'layout_update: '.$handle;
        start_profile($_profilerKey);
        if (empty($this->_moduleLayout)) {
            $this->fetchFileLayoutUpdates();
        }
        foreach ($this->_moduleLayout->$handle as $updateXml) {
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
        stop_profile($_profilerKey);

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

        if (config('layout.cache.layout') && ($layoutStr = Cache::get($cacheKey, false))) {
            $this->_moduleLayout = simplexml_load_string($layoutStr, $elementClass);
        }

        if (empty($layoutStr)) {
            $this->_moduleLayout = $this->getFileLayoutUpdatesXml();
            if (config('layout.cache.layout')) {
                if (config('cache.default') == 'file') {
                    Cache::put($cacheKey, $this->_moduleLayout->asXml(), 0);
                } else {
                    Cache::tags($cacheTags)->put($cacheKey, $this->_moduleLayout->asXml(), 0);
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

        foreach (Finder::create()->files()->name('*.xml')->in(__DIR__.'/../../layout') as $file) {
            $fileStr =  $file->getContents();
            $fileXml = simplexml_load_string($fileStr, $elementClass);

            if (!$fileXml instanceof SimpleXMLElement) {
                continue;
            }

            $layoutStr .= $fileXml->innerXml();
        }

        $layoutXml = simplexml_load_string('<layouts>'.$layoutStr.'</layouts>', $elementClass);

        return $layoutXml;
    }
}
