<?php namespace Ext\Page\Html;

class Footer extends \Ext\Block
{
    protected $_copyright;

    public function setCopyright($copyright)
    {
        $this->_copyright = $copyright;

        return $this;
    }
    public function getCopyright()
    {
        #TODO Need to Update from config
        if (!$this->_copyright) {
            $this->_copyright = '';
        }

        return $this->_copyright;
    }
}
