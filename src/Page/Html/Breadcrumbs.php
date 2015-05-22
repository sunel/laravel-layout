<?php

namespace Layout\Page\Html;

class Breadcrumbs extends \Layout\Block
{
    /**
     * Array of breadcrumbs.
     *
     * array(
     *  [$index] => array(
     *                  ['label']
     *                  ['title']
     *                  ['link']
     *                  ['first']
     *                  ['last']
     *              )
     * )
     *
     * @var array
     */
    protected $_crumbs = null;
    /**
     * Cache key info.
     *
     * @var null|array
     */
    protected $_cacheKeyInfo = null;

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('render::template.page.html.breadcrumbs');
    }
    public function addCrumb($crumbName, $crumbInfo, $after = false)
    {
        $this->_prepareArray($crumbInfo, ['label', 'title', 'link', 'first', 'last', 'readonly']);
        if ((!isset($this->_crumbs[$crumbName])) || (!$this->_crumbs[$crumbName]['readonly'])) {
            $this->_crumbs[$crumbName] = $crumbInfo;
        }

        return $this;
    }
    /**
     * Get cache key informative items.
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        if (null === $this->_cacheKeyInfo) {
            $this->_cacheKeyInfo = parent::getCacheKeyInfo() + [
                'crumbs' => base64_encode(serialize($this->_crumbs)),
                'name'   => $this->getNameInLayout(),
            ];
        }

        return $this->_cacheKeyInfo;
    }
    protected function _toHtml()
    {
        if (is_array($this->_crumbs)) {
            reset($this->_crumbs);
            $this->_crumbs[key($this->_crumbs)]['first'] = true;
            end($this->_crumbs);
            $this->_crumbs[key($this->_crumbs)]['last'] = true;
        }
        $this->assign('crumbs', $this->_crumbs);

        return parent::_toHtml();
    }
}
