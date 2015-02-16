<?php namespace Ext;


class Layout {
	
	
	/**
     * layout xml
     *
     * @var \Ext\Layout\Element
     */
    protected $_xml = null;
	
	public function __construct(\Ext\Layout\Element $element)
    {
        $this->_elementClass = $element;
        $this->setXml(simplexml_load_string('<layout/>', $this->_elementClass));
        
		# TODO need to remove this if not used
        //$this->_update = Mage::getModel('core/layout_update');

    }
	
    public function setXml(\Ext\Layout\Element $node)
    {
        $this->_xml = $node;
        return $this;
    }
}
