<?php namespace Ext;



class Block {
	
	/**
     * Blocks registry
     *
     * @var array
     */
    protected $_blocks = array();

    /**
     * DOMElement
     *
     */
    protected $_node;

    public function __construct($block) {
    	
    	$this->_node = $block;

    }
        
   	public function getChildHtml($name = null)
   	{
   		
   	}
}
