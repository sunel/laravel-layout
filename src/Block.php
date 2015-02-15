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

    public function getClass(){

    	return ($this->_node->getAttribute('class'));
    }

    public function getTemplate(){
    	
    	return $this->_node->getAttribute('template');
    }

    public function getBlockName(){

		return $this->_node->getAttribute('as')?:$this->_node->getAttribute('name');
	}
        
   	public function getChildHtml($name = null)
   	{
   		
   	}
}
