<?php namespace Ext;

use Illuminate\View\Factory as ViewFactory;
use Illuminate\View\View;

class Factory extends ViewFactory {
	
	/**
     * Blocks registry
     *
     * @var array
     */
    protected $_blocks = array();

	/**
	 * Get the evaluated view contents for the given view.
	 *
	 * @param  string  $view
	 * @param  array   $data
	 * @param  array   $mergeData
	 * @return \Illuminate\View\View
	 */
	public function render($handle)
	{
		
		$handle = $this->convertHandler($handle);

		$layout = app('render.layout');

		$view = $this->getView($handle,$layout);

		//$this->callCreator();
		
		return $view;
	}

	public function getView($handle,$layout)
	{	
		$end = [];

		foreach ($layout->get('default') as $_layout) {

			$this->parseNode($_layout);
			
		}
		dd($this->_blocks);

		dd($layout->get($handle));
	}

	protected function parseNode($block)
	{
		$parent = $block->parentNode->nodeName;

		$childNodes = $this->getChildNodes($block);
		foreach ($childNodes as $_child) {
			switch ($_child->nodeName) {
				case 'block':
					$this->generateBlocks($_child,$parent);	
					break;
				
				default:
					# code...
					break;
			}
		}
	}
	protected function generateBlocks($block,$parent)
	{
		if(!$block->hasAttributes()){
			throw new InvalidBlockException("Invalid Block supplied", 1);
		}

		$block_value = [
			'class' => ($block->getAttribute('class')),
			'template' => $block->getAttribute('template'),
		];

		$name = $this->_getBlockName($block);

		$this->_blocks[$name] =  $block_value;
	}

	protected function getChildNodes($element){

		$childNodes = [];
		qp($element)->children()->each(function($index,$node) use(&$childNodes) {
			$childNodes[] = $node; 
		});

		return $childNodes;

	}

	protected function _getBlockName($block){

		return $block->getAttribute('as')?:$block->getAttribute('name');
	}

	protected function convertHandler($handle)
	{
		return str_replace('.','_',strtolower($handle));
	}	

	/**
	 * Call the creator for a given view.
	 *
	 * @param  \Illuminate\View\View  $view
	 * @return void
	 */
	public function callCreator(View $view)
	{
		$this->events->fire('rendering: '.$view->getName(), array($view));
	}

	
}
