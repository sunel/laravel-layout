<?php

namespace Layout\Page\Html;

use Auth;
use Layout\Object;

class Links extends \Layout\Block
{
    /**
     * All links.
     *
     * @var array
     */
    protected $_links = [];
    /**
     * Cache key info.
     *
     * @var null|array
     */
    protected $_cacheKeyInfo = null;
    /**
     * Set default template.
     */
    protected function _construct()
    {
        $this->setTemplate('render::template.page.template.links');
    }
    /**
     * Get all links.
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->_links;
    }
    /**
     * Add link to the list.
     *
     * @param string       $label
     * @param string       $url
     * @param string       $title
     * @param bool         $prepare
     * @param array        $urlParams
     * @param int          $position
     * @param string|array $liParams
     * @param string|array $aParams
     * @param string       $beforeText
     * @param string       $afterText
     *
     * @return \Layout\Page\Html\Links
     */
    public function addLink($label, $url = '', $title = '', $prepare = false, $urlParams = [],
        $position = null, $liParams = null, $aParams = null)
    {
        if (is_null($label) || false === $label) {
            return $this;
        }
		
        $link = new Object();
        $link->setData([
            'label' => $label,
            'url' => ($prepare ? $this->getUrl($url, (is_array($urlParams) ? $urlParams : array())) : $url),
            'title' => $title,
            'li_params' => $this->_prepareParams($liParams),
            'a_params' => $this->_prepareParams($aParams),
        ]);
        $this->_addIntoPosition($link, $position);

        return $this;
    }
	
	public function addClass($names)
	{
		$this->setClassNames($names);
	}
    /**
     * Add link into collection.
     *
     * @param Varien_Object $link
     * @param int           $position
     *
     * @return \Layout\Page\Html\Links
     */
    protected function _addIntoPosition($link, $position)
    {
        $this->_links[$this->_getNewPosition($position)] = $link;
        if (intval($position) > 0) {
            ksort($this->_links);
        }

        return $this;
    }
    /**
     * Add block to link list.
     *
     * @param string $blockName
     *
     * @return \Layout\Page\Html\Links
     */
    public function addLinkBlock($blockName)
    {
        $block = $this->getLayout()->getBlock($blockName);
        if ($block) {
            $position = (int) $block->getPosition();
            $this->_addIntoPosition($block, $position);
        }

        return $this;
    }
    /**
     * Remove Link block by blockName.
     *
     * @param string $blockName
     *
     * @return \Layout\Page\Html\Links
     */
    public function removeLinkBlock($blockName)
    {
        foreach ($this->_links as $key => $link) {
            if ($link instanceof \Layout\Block && $link->getNameInLayout() == $blockName) {
                unset($this->_links[$key]);
            }
        }

        return $this;
    }
    /**
     * Removes link by url.
     *
     * @param string $url
     *
     * @return \Layout\Page\Html\Links
     */
    public function removeLinkByUrl($url)
    {
        foreach ($this->_links as $k => $v) {
            if ($v->getUrl() == $url) {
                unset($this->_links[$k]);
            }
        }

        return $this;
    }
    /**
     * Get cache key informative items
     * Provide string array key to share specific info item with FPC placeholder.
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        if (is_null($this->_cacheKeyInfo)) {
            $links = [];
            if (!empty($this->_links)) {
                foreach ($this->_links as $position => $link) {
                    if ($link instanceof Object) {
                        $links[$position] = $link->getData();
                    }
                }
            }
            $this->_cacheKeyInfo = parent::getCacheKeyInfo() + [
                'links' => base64_encode(serialize($links)),
                'name' => $this->getNameInLayout(),
            ];
        }

        return $this->_cacheKeyInfo;
    }
    /**
     * Prepare tag attributes.
     *
     * @param string|array $params
     *
     * @return string
     */
    protected function _prepareParams($params)
    {
        if (is_string($params)) {
            return $params;
        } elseif (is_array($params)) {
            $result = '';
            foreach ($params as $key => $value) {
                $result .= ' '.$key.'='.addslashes($value).'';
            }

            return $result;
        }

        return '';
    }
    /**
     * Set first/last.
     *
     * @return \Layout\Page\Html\Links
     */
    protected function _beforeToHtml()
    {
        if (!empty($this->_links)) {
            reset($this->_links);
            $this->_links[key($this->_links)]->setIsFirst(true);
            end($this->_links);
            $this->_links[key($this->_links)]->setIsLast(true);
        }

        return parent::_beforeToHtml();
    }
    /**
     * Return new link position in list.
     *
     * @param int $position
     *
     * @return int
     */
    protected function _getNewPosition($position = 0)
    {
        if (intval($position) > 0) {
            while (isset($this->_links[$position])) {
                $position++;
            }
        } else {
            $position = 0;
            foreach ($this->_links as $k => $v) {
                $position = $k;
            }
            $position += 10;
        }

        return $position;
    }
    /**
     * Get tags array for saving cache.
     *
     * @return array
     */
    public function getCacheTags()
    {
        if (Auth::check()) {
            $this->addModelTags(Auth::user());
        }

        return parent::getCacheTags();
    }
}
