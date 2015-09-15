<?php

namespace Layout\Page;

class Html extends \Layout\Block
{
    public function boot()
    {
        $action = $this->routeHandler();
        if ($action) {
            $this->addBodyClass($action);
        }
    }

    /**
     * Add CSS class to page body tag.
     *
     * @param string $className
     *
     * @return \Layout\Page\Html
     */
    public function addBodyClass($className)
    {
        $className = preg_replace('#[^a-z0-9]+#', '-', strtolower($className));
        $this->setBodyClass($this->getBodyClass().' '.$className);

        return $this;
    }

    public function getBodyClass()
    {
        return $this->_getData('body_class');
    }

    protected function routeHandler()
    {
        $route_name = \Route::currentRouteName();

        if (empty($route_name)) {
            return false;
        }

        return str_replace('.', '_', strtolower($route_name));
    }
}
