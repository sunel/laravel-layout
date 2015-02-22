<?php namespace Ext\Page\Html;


class Header extends \Ext\Block {


	public function _construct()
    {
        $this->setTemplate('render::page.html.header');
    }
	
}