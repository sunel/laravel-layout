<?php namespace Ext;

	
class Block extends Object
{
	/**
     * Block name in layout
     *
     * @var string
     */
    protected $_nameInLayout;

    /**
     * Parent layout of the block
     *
     * @var \Ext\Layout
     */
    protected $_layout;

    /**
     * Parent block
     *
     * @var \Ext\Block
     */
    protected $_parent;

    /**
     * Short alias of this block that was refered from parent
     *
     * @var string
     */
    protected $_alias;

    /**
     * Suffix for name of anonymous block
     *
     * @var string
     */
    protected $_anonSuffix;

    /**
     * Contains references to child block objects
     *
     * @var array
     */
    protected $_children = array();

    /**
     * Sorted children list
     *
     * @var array
     */
    protected $_sortedChildren = array();

    /**
     * Children blocks HTML cache array
     *
     * @var array
     */
    protected $_childrenHtmlCache = array();

    /**
     * Arbitrary groups of child blocks
     *
     * @var array
     */
    protected $_childGroups = array();
	
	/**
     * Whether this block was not explicitly named
     *
     * @var boolean
     */
    protected $_isAnonymous = false;

    /**
     * Parent block
     *
     * @var \Ext\Block
     */
    protected $_parentBlock;
	
	 /**
     * Array of block sort priority instructions
     *
     * @var array
     */
    protected $_sortInstructions = array();
	
	 /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template;
	
	protected static $_showTemplateHints;
	
    protected static $_showTemplateHintsBlocks;
	
	
	public function debug()
	{
		dd($this);
	}
	
	/**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * Set path to template used for generating block's output.
     *
     * @param string $template
     * @return Mage_Core_Block_Template
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
        return $this;
    }
	
	/**
     * Retrieve parent block
     *
     * @return \Ext\Block
     */
    public function getParentBlock()
    {
        return $this->_parentBlock;
    }

    /**
     * Set parent block
     *
     * @param   \Ext\Block $block
     * @return  \Ext\Block
     */
    public function setParentBlock(\Ext\Block $block)
    {
        $this->_parentBlock = $block;
        return $this;
    }
	
	/**
     * Set layout object
     *
     * @param   \Ext\Layout $layout
     * @return  \Ext\Block
     */
    public function setLayout(\Ext\Layout $layout)
    {
        $this->_layout = $layout;
        app('events')->fire('block.prepare.layout.before', array('block' => $this));
        $this->_prepareLayout();
        app('events')->fire('block.prepare.layout.after', array('block' => $this));
        return $this;
    }
	
	/**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changing layout
     *
     * @return \Ext\Block
     */
    protected function _prepareLayout()
    {
        return $this;
    }

    /**
     * Retrieve layout object
     *
     * @return \Ext\Layout
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * Check if block is using auto generated (Anonymous) name
     * @return bool
     */
    public function getIsAnonymous()
    {
        return $this->_isAnonymous;
    }

    /**
     * Set the anonymous flag
     *
     * @param  bool $flag
     * @return \Ext\Block
     */
    public function setIsAnonymous($flag)
    {
        $this->_isAnonymous = (bool)$flag;
        return $this;
    }
	
	/**
     * Returns anonymous block suffix
     *
     * @return string
     */
    public function getAnonSuffix()
    {
        return $this->_anonSuffix;
    }

    /**
     * Set anonymous suffix for current block
     *
     * @param string $suffix
     * @return \Ext\Block
     */
    public function setAnonSuffix($suffix)
    {
        $this->_anonSuffix = $suffix;
        return $this;
    }
	
	/**
     * Returns block alias
     *
     * @return string
     */
    public function getBlockAlias()
    {
        return $this->_alias;
    }

    /**
     * Set block alias
     *
     * @param string $alias
     * @return \Ext\Block
     */
    public function setBlockAlias($alias)
    {
        $this->_alias = $alias;
        return $this;
    }
	
	/**
     * Set block's name in layout and unsets previous link if such exists.
     *
     * @param string $name
     * @return \Ext\Block
     */
    public function setNameInLayout($name)
    {
        if (!empty($this->_nameInLayout) && $this->getLayout()) {
            $this->getLayout()->unsetBlock($this->_nameInLayout)
                ->setBlock($name, $this);
        }
        $this->_nameInLayout = $name;
        return $this;
    }
	
	/**
     * Alias for getName method.
     *
     * @return string
     */
    public function getNameInLayout()
    {
        return $this->_nameInLayout;
    }
	

    /**
     * Retrieve sorted list of children.
     *
     * @return array
     */
    public function getSortedChildren()
    {
        $this->sortChildren();
        return $this->_sortedChildren;
    }
	
	
	/**
     * Set block attribute value
     *
     * Wrapper for method "setData"
     *
     * @param   string $name
     * @param   mixed $value
     * @return  \Ext\Block
     */
    public function setAttribute($name, $value = null)
    {
        return $this->setData($name, $value);
    }

    /**
     * Set child block
     *
     * @param   string $alias
     * @param   \Ext\Block $block
     * @return  \Ext\Block
     */
    public function setChild($alias, $block)
    {
        if (is_string($block)) {
            $block = $this->getLayout()->getBlock($block);
        }
        if (!$block) {
            return $this;
        }

        if ($block->getIsAnonymous()) {
            $suffix = $block->getAnonSuffix();
            if (empty($suffix)) {
                $suffix = 'child' . sizeof($this->_children);
            }
            $blockName = $this->getNameInLayout() . '.' . $suffix;

            if ($this->getLayout()) {
                $this->getLayout()->unsetBlock($block->getNameInLayout())
                    ->setBlock($blockName, $block);
            }

            $block->setNameInLayout($blockName);
            $block->setIsAnonymous(false);

            if (empty($alias)) {
                $alias = $blockName;
            }
        }

        $block->setParentBlock($this);
        $block->setBlockAlias($alias);
        $this->_children[$alias] = $block;
        return $this;
    }

    /**
     * Unset child block
     *
     * @param  string $alias
     * @return \Ext\Block
     */
    public function unsetChild($alias)
    {
        if (isset($this->_children[$alias])) {
            /** @var \Ext\Block $block */
            $block = $this->_children[$alias];
            $name = $block->getNameInLayout();
            unset($this->_children[$alias]);
            $key = array_search($name, $this->_sortedChildren);
            if ($key !== false) {
                unset($this->_sortedChildren[$key]);
            }
        }

        return $this;
    }
	
	/**
     * Append child block
     *
     * @param   \Ext\Block|string $block
     * @param   string $alias
     * @return  \Ext\Block
     */
    public function append($block, $alias = '')
    {
        $this->insert($block, '', true, $alias);
        return $this;
    }
	
	/**
     * Insert child block
     *
     * @param   \Ext\Block|string $block
     * @param   string $siblingName
     * @param   boolean $after
     * @param   string $alias
     * @return  object $this
     */
    public function insert($block, $siblingName = '', $after = false, $alias = '')
    {
        if (is_string($block)) {
            $block = $this->getLayout()->getBlock($block);
        }
        if (!$block) {
            /*
             * if we don't have block - don't throw exception because
             * block can simply removed using layout method remove
             */
            return $this;
        }
        if ($block->getIsAnonymous()) {
            $this->setChild('', $block);
            $name = $block->getNameInLayout();
        } elseif ('' != $alias) {
            $this->setChild($alias, $block);
            $name = $block->getNameInLayout();
        } else {
            $name = $block->getNameInLayout();
            $this->setChild($name, $block);
        }

        if ($siblingName === '') {
            if ($after) {
                array_push($this->_sortedChildren, $name);
            } else {
                array_unshift($this->_sortedChildren, $name);
            }
        } else {
            $key = array_search($siblingName, $this->_sortedChildren);
            if (false !== $key) {
                if ($after) {
                    $key++;
                }
                array_splice($this->_sortedChildren, $key, 0, $name);
            } else {
                if ($after) {
                    array_push($this->_sortedChildren, $name);
                } else {
                    array_unshift($this->_sortedChildren, $name);
                }
            }

            $this->_sortInstructions[$name] = array($siblingName, (bool)$after, false !== $key);
        }

        return $this;
    }

	/**
     * Call a child and unset it, if callback matched result
     *
     * $params will pass to child callback
     * $params may be array, if called from layout with elements with same name, for example:
     * ...<foo>value_1</foo><foo>value_2</foo><foo>value_3</foo>
     *
     * Or, if called like this:
     * ...<foo>value_1</foo><bar>value_2</bar><baz>value_3</baz>
     * - then it will be $params1, $params2, $params3
     *
     * It is no difference anyway, because they will be transformed in appropriate way.
     *
     * @param string $alias
     * @param string $callback
     * @param mixed $result
     * @param array $params
     * @return \Ext\Block
     */
    public function unsetCallChild($alias, $callback, $result, $params)
    {
        $child = $this->getChild($alias);
        if ($child) {
            $args = func_get_args();
            $alias = array_shift($args);
            $callback = array_shift($args);
            $result = (string)array_shift($args);
            if (!is_array($params)) {
                $params = $args;
            }

            if ($result == call_user_func_array(array(&$child, $callback), $params)) {
                $this->unsetChild($alias);
            }
        }
        return $this;
    }

    /**
     * Unset all children blocks
     *
     * @return \Ext\Block
     */
    public function unsetChildren()
    {
        $this->_children = array();
        $this->_sortedChildren = array();
        return $this;
    }

    /**
     * Retrieve child block by name
     *
     * @param  string $name
     * @return mixed
     */
    public function getChild($name = '')
    {
        if ($name === '') {
            return $this->_children;
        } elseif (isset($this->_children[$name])) {
            return $this->_children[$name];
        }
        return false;
    }

    /**
     * Retrieve child block HTML
     *
     * @param   string $name
     * @param   boolean $useCache
     * @param   boolean $sorted
     * @return  string
     */
    public function getChildHtml($name = '', $useCache = true, $sorted = false)
    {
        if ($name === '') {
            if ($sorted) {
                $children = array();
                foreach ($this->getSortedChildren() as $childName) {
                    $children[$childName] = $this->getLayout()->getBlock($childName);
                }
            } else {
                $children = $this->getChild();
            }
            $out = '';
            foreach ($children as $child) {
                $out .= $this->_getChildHtml($child->getBlockAlias(), $useCache);
            }
            return $out;
        } else {
            return $this->_getChildHtml($name, $useCache);
        }
    }

    /**
     * @param string $name          Parent block name
     * @param string $childName     OPTIONAL Child block name
     * @param bool $useCache        OPTIONAL Use cache flag
     * @param bool $sorted          OPTIONAL @see getChildHtml()
     * @return string
     */
    public function getChildChildHtml($name, $childName = '', $useCache = true, $sorted = false)
    {
        if (empty($name)) {
            return '';
        }
        $child = $this->getChild($name);
        if (!$child) {
            return '';
        }
        return $child->getChildHtml($childName, $useCache, $sorted);
    }

    /**
     * Obtain sorted child blocks
     *
     * @return array
     */
    public function getSortedChildBlocks()
    {
        $children = array();
        foreach ($this->getSortedChildren() as $childName) {
            $children[$childName] = $this->getLayout()->getBlock($childName);
        }
        return $children;
    }

    /**
     * Retrieve child block HTML
     *
     * @param   string $name
     * @param   boolean $useCache
     * @return  string
     */
    protected function _getChildHtml($name, $useCache = true)
    {
        if ($useCache && isset($this->_childrenHtmlCache[$name])) {
            return $this->_childrenHtmlCache[$name];
        }

        $child = $this->getChild($name);

        $this->_childrenHtmlCache[$name] = $html;
        return $html;
    }
	
	/**
     * Retrieve block html
     *
     * @param   string $name
     * @return  string
     */
    public function getBlockHtml($name)
    {
        if (!($layout = $this->getLayout())) {
            return '';
        }
        if (!($block = $layout->getBlock($name))) {
            return '';
        }
        return $block->toHtml();
    }
	
	/**
     * Sort block's children
     *
     * @param boolean $force force re-sort all children
     * @return \Ext\Block
     */
    public function sortChildren($force = false)
    {
        foreach ($this->_sortInstructions as $name => $list) {
            list($siblingName, $after, $exists) = $list;
            if ($exists && !$force) {
                continue;
            }
            $this->_sortInstructions[$name][2] = true;

            $index = array_search($name, $this->_sortedChildren);
            $siblingKey = array_search($siblingName, $this->_sortedChildren);

            if ($index === false || $siblingKey === false) {
                continue;
            }

            if ($after) {
                // insert after block
                if ($index == $siblingKey + 1) {
                    continue;
                }
                // remove sibling from array
                array_splice($this->_sortedChildren, $index, 1, array());
                // insert sibling after
                array_splice($this->_sortedChildren, $siblingKey + 1, 0, array($name));
            } else {
                // insert before block
                if ($index == $siblingKey - 1) {
                    continue;
                }
                // remove sibling from array
                array_splice($this->_sortedChildren, $index, 1, array());
                // insert sibling after
                array_splice($this->_sortedChildren, $siblingKey, 0, array($name));
            }
        }

        return $this;
    }

	/**
     * Make sure specified block will be registered in the specified child groups
     *
     * @param string $groupName
     * @param \Ext\Block $child
     */
    public function addToChildGroup($groupName, \Ext\Block $child)
    {
        if (!isset($this->_childGroups[$groupName])) {
            $this->_childGroups[$groupName] = array();
        }
        if (!in_array($child->getBlockAlias(), $this->_childGroups[$groupName])) {
            $this->_childGroups[$groupName][] = $child->getBlockAlias();
        }
    }

    /**
     * Add self to the specified group of parent block
     *
     * @param string $groupName
     * @return \Ext\Block
     */
    public function addToParentGroup($groupName)
    {
        $this->getParentBlock()->addToChildGroup($groupName, $this);
        return $this;
    }

    /**
     * Get a group of child blocks
     *
     * Returns an array of <alias> => <block>
     * or an array of <alias> => <callback_result>
     * The callback currently supports only $this methods and passes the alias as parameter
     *
     * @param string $groupName
     * @param string $callback
     * @param bool $skipEmptyResults
     * @return array
     */
    public function getChildGroup($groupName, $callback = null, $skipEmptyResults = true)
    {
        $result = array();
        if (!isset($this->_childGroups[$groupName])) {
            return $result;
        }
        foreach ($this->getSortedChildBlocks() as $block) {
            $alias = $block->getBlockAlias();
            if (in_array($alias, $this->_childGroups[$groupName])) {
                if ($callback) {
                    $row = $this->$callback($alias);
                    if (!$skipEmptyResults || $row) {
                        $result[$alias] = $row;
                    }
                } else {
                    $result[$alias] = $block;
                }

            }
        }
        return $result;
    }

    /**
     * Get a value from child block by specified key
     *
     * @param string $alias
     * @param string $key
     * @return mixed
     */
    public function getChildData($alias, $key = '')
    {
        $child = $this->getChild($alias);
        if ($child) {
            return $child->getData($key);
        }
    }
	
	/**
     * Load block html from cache storage
     *
     * @return string | false
     */
    protected function _loadCache()
    {
        if (is_null($this->getCacheLifetime()) || !$this->_getApp()->useCache(self::CACHE_GROUP)) {
            return false;
        }
        $cacheKey = $this->getCacheKey();
        /** @var $session Mage_Core_Model_Session */
        $session = Mage::getSingleton('core/session');
        $cacheData = $this->_getApp()->loadCache($cacheKey);
        if ($cacheData) {
            $cacheData = str_replace(
                $this->_getSidPlaceholder($cacheKey),
                $session->getSessionIdQueryParam() . '=' . $session->getEncryptedSessionId(),
                $cacheData
            );
        }
        return $cacheData;
    }

    /**
     * Save block content to cache storage
     *
     * @param string $data
     * @return Mage_Core_Block_Abstract
     */
    protected function _saveCache($data)
    {
        if (is_null($this->getCacheLifetime()) || !$this->_getApp()->useCache(self::CACHE_GROUP)) {
            return false;
        }
        $cacheKey = $this->getCacheKey();
        /** @var $session Mage_Core_Model_Session */
        $session = Mage::getSingleton('core/session');
        $data = str_replace(
            $session->getSessionIdQueryParam() . '=' . $session->getEncryptedSessionId(),
            $this->_getSidPlaceholder($cacheKey),
            $data
        );

        $tags = $this->getCacheTags();

        $this->_getApp()->saveCache($data, $cacheKey, $tags, $this->getCacheLifetime());
        $this->_getApp()->saveCache(
            json_encode($tags),
            $this->_getTagsCacheKey($cacheKey),
            $tags,
            $this->getCacheLifetime()
        );
        return $this;
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return \Ext\Block
     */
    protected function _beforeToHtml()
    {
        return $this;
    }

    /**
     * Produce and return block's html output
     *
     * It is a final method, but you can override _toHtml() method in descendants if needed.
     *
     * @return string
     */
    final public function toHtml()
    {
        app('events')->fire('block.to.html.before', array('block' => $this));
 		
		# TODO Need to implement this
        //$html = $this->_loadCache();
		
        if ($html === false) {
        	
            $this->_beforeToHtml();
            $html = $this->_toHtml();
			
			# TODO Need to implement this
            //$this->_saveCache($html);
        }
		
        $html = $this->_afterToHtml($html);

        /**
         * Use single transport object instance for all blocks
         */
        if (self::$_transportObject === null) {
            self::$_transportObject = new Object;
        }
        self::$_transportObject->setHtml($html);
		
        app('events')->fire('block.to.html.after',
            array('block' => $this, 'transport' => self::$_transportObject));
			
        $html = self::$_transportObject->getHtml();

        return $html;
    }

    /**
     * Processing block html after rendering
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        return $html;
    }

   /**
     * Check if direct output is allowed for block
     *
     * @return bool
     */
    public function getDirectOutput()
    {
        if ($this->getLayout()) {
            return $this->getLayout()->getDirectOutput();
        }
        return false;
    }

    public function getShowTemplateHints()
    {
    	# TODO Need to Implement this
        if (is_null(self::$_showTemplateHints)) {
            
        }
        return self::$_showTemplateHints;
    }

    /**
     * Retrieve block view from file (template)
     *
     * @param   string $fileName
     * @return  string
     */
    public function fetchView($fileName)
    {
        //Debugbar::startMeasure($fileName);

        // EXTR_SKIP protects from overriding
        // already defined variables
        extract ($this->_viewVars, EXTR_SKIP);
        $do = $this->getDirectOutput();

        if (!$do) {
            ob_start();
        }
        if ($this->getShowTemplateHints()) {
            echo <<<HTML
<div style="position:relative; border:1px dotted red; margin:6px 2px; padding:18px 2px 2px 2px; zoom:1;">
<div style="position:absolute; left:0; top:0; padding:2px 5px; background:red; color:white; font:normal 11px Arial;
text-align:left !important; z-index:998;" onmouseover="this.style.zIndex='999'"
onmouseout="this.style.zIndex='998'" title="{$fileName}">{$fileName}</div>
HTML;
            if (self::$_showTemplateHintsBlocks) {
                $thisClass = get_class($this);
                echo <<<HTML
<div style="position:absolute; right:0; top:0; padding:2px 5px; background:red; color:blue; font:normal 11px Arial;
text-align:left !important; z-index:998;" onmouseover="this.style.zIndex='999'" onmouseout="this.style.zIndex='998'"
title="{$thisClass}">{$thisClass}</div>
HTML;
            }
        }

        try {
            $includeFilePath = realpath($this->_viewDir . DS . $fileName);
            if (strpos($includeFilePath, realpath($this->_viewDir)) === 0 || $this->_getAllowSymlinks()) {
                include $includeFilePath;
            } else {
                \Log::info('Not valid template file:'.$fileName);
            }

        } catch (Exception $e) {
            ob_get_clean();
            throw $e;
        }

        if ($this->getShowTemplateHints()) {
            echo '</div>';
        }

        if (!$do) {
            $html = ob_get_clean();
        } else {
            $html = '';
        }
        //Debugbar::stopMeasure($fileName);
        return $html;
    }

    /**
     * Render block
     *
     * @return string
     */
    public function renderView()
    {
        $html = $this->fetchView($this->getTemplateFile());
        return $html;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            return '';
        }
        $html = $this->renderView();
        return $html;
    }
	

    /**
     * Get chilren blocks count
     * @return int
     */
    public function countChildren()
    {
        return count($this->_children);
    }

}