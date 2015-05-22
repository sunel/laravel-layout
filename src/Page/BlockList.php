<?php

namespace Layout\Page;

class BlockList extends Text
{
    protected function _toHtml()
    {
        $this->setText('');
        foreach ($this->getSortedChildren() as $name) {
            $block = $this->getLayout()->getBlock($name);
            if (!$block) {
                throw new \Layout\InvalidBlockException('Invalid block type:'.$block);
            }
            $this->addText($block->toHtml());
        }

        return parent::_toHtml();
    }
}
