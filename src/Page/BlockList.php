<?php namespace Ext\Page;

class BlockList extends Text
{
    protected function _toHtml()
    {
        $this->setText('');
        foreach ($this->getSortedChildren() as $name) {
            $block = $this->getLayout()->getBlock($name);
            if (!$block) {
                throw new \Ext\InvalidBlockException('Invalid block type:'.$block);
            }
            $this->addText($block->toHtml());
        }

        return parent::_toHtml();
    }
}
