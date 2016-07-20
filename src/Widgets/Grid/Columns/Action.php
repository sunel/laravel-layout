<?php

namespace Layout\Widgets\Grid\Columns; 

use ViewComponents\Grids\Component\Column;
use ViewComponents\ViewComponents\Component\Compound;
use ViewComponents\ViewComponents\Component\DataView;

class Action extends Column
{

    protected $actions;

    /**
     * Constructor.
     *
     * @param string|null $columnId unique column name for internal usage
     * @param string|null $name column name
     */
    public function __construct($actions)
    {
        $this->setDestinationParentId(Compound::ROOT_ID);
        $this->setId('actions');
        $this->setLabel('Actions');
        $this->titleView = new DataView(null, [$this, 'getLabel']);
        $this->dataView = new DataView(null, [$this, 'getCurrentValueFormatted']);
        $this->setActions($actions);
    }

     /**
     * Returns formatted value of current data cell.
     *
     * @return string
     */
    public function getCurrentValueFormatted()
    {
        $html = [];

        foreach ($this->getActions() as $lable => $action) {
            $url = call_user_func($action, $this->getGrid()->getCurrentRow());
            $html[] = "<a href='$url'>$lable</a>"; 
        }

        return implode(' | ', $html);
    }

    /**
     * Formats value extracted from data row.
     *
     * @param $value
     * @return string
     */
    public function formatValue($value)
    {
        return;        
    }

    /**
     * @return callable
     */
    public function getActions()
    {
        return $this->actions;
    }
    /**
     * @param callable $urlGenerator
     * @return $this
     */
    public function setActions(array $actions)
    {
        $this->actions = $actions;
        return $this;
    }
}