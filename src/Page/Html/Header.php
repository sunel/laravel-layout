<?php namespace Layout\Page\Html;

class Header extends \Layout\Block
{
    public function _construct()
    {
        $this->setTemplate('render::template.page.html.header');
    }
}
