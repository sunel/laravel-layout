<?php

namespace Layout\Page\Html;

class CookieNotice extends \Layout\Block
{
    public function getCookieRestrictionBlockContent()
    {
        return config('layout.cookienotice.content');
    }
}
