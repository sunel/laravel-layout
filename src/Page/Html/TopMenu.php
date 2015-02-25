<?php namespace Layout\Page\Html;

use Carbon\Carbon;

class TopMenu extends \Layout\Block
{
    /**
     * Top menu data tree.
     *
     * @var
     */
    protected $_menu;
    /**
     * Current entity key.
     *
     * @var string|int
     */
    protected $_currentEntityKey;

    /**
     * Init top menu tree structure.
     */
    public function _construct()
    {
        $this->_menu = '';
        $this->addData([
            'cache_lifetime' => Carbon::now()->addMinutes(10),
        ]);
    }

    /**
     * Get top menu html.
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     *
     * @return string
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '')
    {
        app('events')->fire('page.block.html.topmenu.gethtml.before', [
            'menu'  => $this->_menu,
            'block' => $this,
        ]);

        $html = $this->_getHtml($this->_menu, $childrenWrapClass);

        app('events')->fire('page.block.html.topmenu.gethtml.after', [
            'menu' => $this->_menu,
            'html' => $html,
        ]);

        return $html;
    }

    protected function _getHtml($menuTree, $childrenWrapClass)
    {
        #TODO need to implement this
        $html = '<ul class="nav navbar-nav">
                    <li><a href="/">Home</a></li>
                </ul>';

        return $html;
    }

    /**
     * Returns array of menu item's classes.
     *
     * @param  $item
     *
     * @return array
     */
    protected function _getMenuItemClasses($item)
    {
        $classes = [];
        $classes[] = 'level'.$item->getLevel();
        $classes[] = $item->getPositionClass();
        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }
        if ($item->getIsActive()) {
            $classes[] = 'active';
        }
        if ($item->getIsLast()) {
            $classes[] = 'last';
        }
        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }
        if ($item->hasChildren()) {
            $classes[] = 'parent';
        }

        return $classes;
    }

    /**
     * Retrieve cache key data.
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheId = [
            'TOPMENU',
            $this->getNameInLayout(),
        ];

        return $cacheId;
    }
}
