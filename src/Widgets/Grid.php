<?php

namespace Layout\Widgets; 

use ViewComponents\Eloquent\EloquentDataProvider;
use ViewComponents\Grids\Grid as ViewComponentsGrid;
use ViewComponents\ViewComponents\Customization\CssFrameworks\BootstrapStyling;

abstract class Grid extends \Layout\Block
{
	abstract protected function getDataModel();

	abstract public function getGridTitle();

    abstract protected function getComponent();

    protected function getDataProvider()
    {
    	return new EloquentDataProvider($this->getDataModel());
    }

    protected function getGrid()
    {
    	return new ViewComponentsGrid($this->getDataProvider(),$this->getComponent());
    }

    public function getGridHtml() 
    {

		$bootstrap = new BootstrapStyling();
		$bootstrap->apply($grid = $this->getGrid());

    	return $grid->render();
    }


}