<?php

namespace Layout\Page\Html;

class Footer extends \Layout\Block
{
    protected $_copyright;

    public function setCopyright($copyright)
    {
        $this->_copyright = $copyright;

        return $this;
    }
    public function getCopyright()
    {
        if (!$this->_copyright) {
            $this->_copyright = config('layout.footer.copyright');
        }

        return $this->_copyright;
    }
}
