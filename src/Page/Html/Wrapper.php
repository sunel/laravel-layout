<?php

namespace Layout\Page\Html;

class Wrapper extends \Layout\Block
{
    /**
     * Whether block should render its content if there are no children (no).
     *
     * @var bool
     */
    protected $dependsOnChildren = true;

    /**
     * Render the wrapper element html
     * Supports different optional parameters, set in data by keys:
     * - element_tag_name (div by default)
     * - element_id
     * - element_class
     * - element_other_attributes.
     *
     * Renders all children inside the element.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = empty($this->children) ? '' : trim($this->getChildHtml('', true, true));
        if ($this->dependsOnChildren && empty($html)) {
            return '';
        }
        if ($this->_isInvisible()) {
            return $html;
        }
        $id = $this->hasElementId() ? sprintf(' id="%s"', $this->getElementId()) : '';
        $class = $this->hasElementClass() ? sprintf(' class="%s"', $this->getElementClass()) : '';
        $otherParams = $this->hasOtherParams() ? ' '.$this->getOtherParams() : '';

        return sprintf('<%1$s%2$s%3$s%4$s>%5$s</%1$s>', $this->getElementTagName(), $id, $class, $otherParams, $html);
    }
    /**
     * Wrapper element tag name getter.
     *
     * @return string
     */
    public function getElementTagName()
    {
        $tagName = $this->_getData('html_tag_name');

        return $tagName ? $tagName : 'div';
    }
    /**
     * Setter whether this block depends on children.
     *
     * @param $depends
     *
     * @return \Layout\Page\Html\Wrapper
     */
    public function dependsOnChildren($depends = '0')
    {
        $this->dependsOnChildren = (bool) (int) $depends;

        return $this;
    }
    /**
     * Whether the wrapper element should be eventually rendered
     * If it becomes "invisible", the behaviour will be somewhat similar to \Layout\Page\BlockList.
     *
     * @return bool
     */
    protected function _isInvisible()
    {
        if (!$this->hasMayBeInvisible()) {
            return false;
        }
        foreach ($this->children as $child) {
            if ($child->hasWrapperMustBeVisible()) {
                return false;
            }
        }

        return true;
    }
}
