<?php

namespace Layout\Page\Html;

use Menu;
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
        $this->addData([
            'cache_lifetime' => Carbon::now()->addMinutes(10),
        ]);
    }

    /**
     * Before rendering html, but after trying to load cache.
     *
     * @return \Layout\Block
     */
    protected function _beforeToHtml()
    {
        $this->_menu = Menu::make('topMenu', function ($menu) {
            $menu->add('Home', '');
        });

        return $this;
    }

    /**
     * Get top menu html.
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     *
     * @return string
     */
    public function getMenus()
    {
        app('events')->fire('page.block.html.topmenu.getMenus.before', [
            'menu' => $this->_menu,
            'block' => $this,
        ]);

        $html = $this->_getHtml($this->_menu);

        app('events')->fire('page.block.html.topmenu.getMenus.after', [
            'menu' => $this->_menu,
            'html' => $html,
        ]);

        return $html;
    }

    protected function _getHtml($menuTree)
    {
        return $menuTree->roots();
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
