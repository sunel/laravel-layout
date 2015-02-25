<?php namespace Layout;

use Debugbar;
use Layout\Layout\Element;
use Layout\Exceptions\InvalidBlockException;

class Layout
{
    /**
     * layout xml.
     *
     * @var \Layout\Layout\Element
     */
    protected $_xml = null;

    /**
     * Class name of simplexml elements for this configuration.
     *
     * @var string
     */
    protected $_elementClass;

    /**
     * Layout Update module.
     *
     * @var \Layout\Layout\Update
     */
    protected $_update;

    /**
     * Blocks registry.
     *
     * @var array
     */
    protected $_blocks = [];

    /**
     * Cache of block callbacks to output during rendering.
     *
     * @var array
     */
    protected $_output = [];

    /**
     * Flag to have blocks' output go directly to browser as oppose to return result.
     *
     * @var boolean
     */
    protected $_directOutput = false;

    /**
     * Event Instance.
     *
     * @var Illuminate\Events\Dispatcher
     */
    protected $events;

    /**
     * Application Instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $_app;

    public function __construct(\Illuminate\Foundation\Application $app)
    {
        $this->_app = $app;
        $this->_elementClass = Element::class;
        $this->setXml(simplexml_load_string('<layout/>', $this->_elementClass));
        $this->events = $this->_app['events'];
        $this->_update = $this->_app['render.layout.update'];
    }

    /**
     * Layout update instance.
     *
     * @return \Layout\Layout\Update
     */
    public function getUpdate()
    {
        return $this->_update;
    }

    /**
     * Declaring layout direct output flag.
     *
     * @param bool $flag
     *
     * @return \Layout\Layout
     */
    public function setDirectOutput($flag)
    {
        $this->_directOutput = $flag;

        return $this;
    }

    /**
     * Retrieve derect output flag.
     *
     * @return bool
     */
    public function getDirectOutput()
    {
        return $this->_directOutput;
    }

    /**
     * Layout xml generation.
     *
     * @return \Layout\Layout
     */
    public function generateXml()
    {
        $xml = $this->getUpdate()->asSimplexml();

        $removeInstructions = $xml->xpath("//remove");
        if (is_array($removeInstructions)) {
            foreach ($removeInstructions as $infoNode) {
                $attributes = $infoNode->attributes();
                $blockName = (string) $attributes->name;
                if ($blockName) {
                    $ignoreNodes = $xml->xpath("//block[@name='".$blockName."']");
                    if (!is_array($ignoreNodes)) {
                        continue;
                    }
                    $ignoreReferences = $xml->xpath("//reference[@name='".$blockName."']");
                    if (is_array($ignoreReferences)) {
                        $ignoreNodes = array_merge($ignoreNodes, $ignoreReferences);
                    }
                    foreach ($ignoreNodes as $block) {
                        if ($block->getAttribute('ignore') !== null) {
                            continue;
                        }
                        if (!isset($block->attributes()->ignore)) {
                            $block->addAttribute('ignore', true);
                        }
                    }
                }
            }
        }
        $this->setXml($xml);

        return $this;
    }

    /**
     * Create layout blocks hierarchy from layout xml configuration.
     *
     * @param \Layout\Layout\Element|null $parent
     */
    public function generateBlocks($parent = null)
    {
        if (empty($parent)) {
            $parent = $this->getNode();
        }
        foreach ($parent as $node) {
            $attributes = $node->attributes();
            if ((bool) $attributes->ignore) {
                continue;
            }
            switch ($node->getName()) {
                case 'block':
                    $this->_generateBlock($node, $parent);
                    $this->generateBlocks($node);
                    break;

                case 'reference':
                    $this->generateBlocks($node);
                    break;

                case 'action':
                    $this->_generateAction($node, $parent);
                    break;
            }
        }
    }

    /**
     * Add block object to layout based on xml node data.
     *
     * @param \Layout\Layout\Element $node
     * @param \Layout\Layout\Element $parent
     *
     * @return \Layout\Layout
     */
    protected function _generateBlock($node, $parent)
    {
        $className = (string) $node['class'];
        $blockName = (string) $node['name'];
        $_profilerKey = 'BLOCK: '.$blockName;

        Debugbar::startMeasure($_profilerKey);

        $block = $this->addBlock($className, $blockName);
        if (!$block) {
            return $this;
        }

        if (!empty($node['parent'])) {
            $parentBlock = $this->getBlock((string) $node['parent']);
        } else {
            $parentName = $parent->getBlockName();
            if (!empty($parentName)) {
                $parentBlock = $this->getBlock($parentName);
            }
        }
        if (!empty($parentBlock)) {
            $alias = isset($node['as']) ? (string) $node['as'] : '';
            if (isset($node['before'])) {
                $sibling = (string) $node['before'];
                if ('-' === $sibling) {
                    $sibling = '';
                }
                $parentBlock->insert($block, $sibling, false, $alias);
            } elseif (isset($node['after'])) {
                $sibling = (string) $node['after'];
                if ('-' === $sibling) {
                    $sibling = '';
                }
                $parentBlock->insert($block, $sibling, true, $alias);
            } else {
                $parentBlock->append($block, $alias);
            }
        }
        if (!empty($node['template'])) {
            $block->setTemplate((string) $node['template']);
        }

        if (!empty($node['output'])) {
            $method = (string) $node['output'];
            $this->addOutputBlock($blockName, $method);
        }

        Debugbar::stopMeasure($_profilerKey);

        return $this;
    }

    /**
     * Enter description here...
     *
     * @param \Layout\Layout\Element $node
     * @param \Layout\Layout\Element $parent
     *
     * @return \Layout\Layout
     */
    protected function _generateAction($node, $parent)
    {
        # TODO Need to implement this

        #if (isset($node['ifconfig']) && ($configPath = (string)$node['ifconfig'])) {
         #   if () {
          #      return $this;
           # }
        #}

        $method = (string) $node['method'];
        if (!empty($node['block'])) {
            $parentName = (string) $node['block'];
        } else {
            $parentName = $parent->getBlockName();
        }

        $_profilerKey = 'BLOCK ACTION: '.$parentName.' -> '.$method;

        Debugbar::startMeasure($_profilerKey);

        if (!empty($parentName)) {
            $block = $this->getBlock($parentName);
        }
        if (!empty($block)) {
            $args = (array) $node->children();
            unset($args['@attributes']);

            foreach ($args as $key => $arg) {
                if (($arg instanceof \Layout\Layout\Element)) {
                    if (isset($arg['helper'])) {
                        $helperName = explode('/', (string) $arg['helper']);
                        $helperMethod = array_pop($helperName);
                        $helperName = implode('/', $helperName);
                        $arg = $arg->asArray();
                        unset($arg['@']);
                        $args[$key] = call_user_func_array([app($helperName), $helperMethod], $arg);
                    } else {
                        /*
                         * if there is no helper we hope that this is assoc array
                         */
                        $arr = [];
                        foreach ($arg as $subkey => $value) {
                            $arr[(string) $subkey] = $value->asArray();
                        }
                        if (!empty($arr)) {
                            $args[$key] = $arr;
                        }
                    }
                }
            }

            if (isset($node['json'])) {
                $json = explode(' ', (string) $node['json']);
                foreach ($json as $arg) {
                    $args[$arg] = json_decode($args[$arg]);
                }
            }

            $this->_translateLayoutNode($node, $args);
            call_user_func_array([$block, $method], $args);
        }

        Debugbar::stopMeasure($_profilerKey);

        return $this;
    }

    /**
     * Translate layout node.
     *
     * @param \Layout\Layout\Element $node
     * @param array               $args
     **/
    protected function _translateLayoutNode($node, &$args)
    {
        if (isset($node['translate'])) {
            // Translate value by core module if module attribute was not set
            $moduleName = (isset($node['module'])) ? (string) $node['module'] : 'core';

            // Handle translations in arrays if needed
            $translatableArguments = explode(' ', (string) $node['translate']);
            foreach ($translatableArguments as $translatableArgumentName) {

               #TODO Need to implement
            }
        }
    }

    /**
     * Save block in blocks registry.
     *
     * @param string      $name
     * @param \Layout\Layout $block
     */
    public function setBlock($name, $block)
    {
        $this->_blocks[$name] = $block;

        return $this;
    }

    /**
     * Remove block from registry.
     *
     * @param string $name
     */
    public function unsetBlock($name)
    {
        $this->_blocks[$name] = null;
        unset($this->_blocks[$name]);

        return $this;
    }

    /**
     * Block Factory.
     *
     * @param string $type
     * @param string $name
     * @param array  $attributes
     *
     * @return \Layout\Block
     */
    public function createBlock($class, $name = '', array $attributes = [])
    {
        try {
            $block = $this->_getBlockInstance($class, $attributes);
        } catch (Exception $e) {
            \Log::exception($e);

            return false;
        }

        if (empty($name) || '.' === $name{0}) {
            $block->setIsAnonymous(true);
            if (!empty($name)) {
                $block->setAnonSuffix(substr($name, 1));
            }
            $name = 'ANONYMOUS_'.sizeof($this->_blocks);
        }

        $block->setClass($class);
        $block->setNameInLayout($name);
        $block->addData($attributes);
        $block->setLayout($this);

        $this->_blocks[$name] = $block;

        $this->events->fire('layout.block.create.after', ['block' => $block]);

        return $this->_blocks[$name];
    }

    /**
     * Add a block to registry, create new object if needed.
     *
     * @param string|\Layout\Block $blockClass
     * @param string            $blockName
     *
     * @return \Layout\Block
     */
    public function addBlock($block, $blockName)
    {
        return $this->createBlock($block, $blockName);
    }

    /**
     * Create block object instance based on block type.
     *
     * @param string $block
     * @param array  $attributes
     *
     * @return \Layout\Block
     */
    protected function _getBlockInstance($block, array $attributes = [])
    {
        if (is_string($block)) {
            if (class_exists($block, true)) {
                $block = app($block);

                $block->addData($attributes);
            }
        }
        if (!$block instanceof \Layout\Block) {
            throw new InvalidBlockException('Invalid block type:'.$block);
        }

        return $block;
    }

    /**
     * Retrieve all blocks from registry as array.
     *
     * @return array
     */
    public function getAllBlocks()
    {
        return $this->_blocks;
    }

    /**
     * Get block object by name.
     *
     * @param string $name
     *
     * @return \Layout\Block
     */
    public function getBlock($name)
    {
        if (isset($this->_blocks[$name])) {
            return $this->_blocks[$name];
        } else {
            return false;
        }
    }

    /**
     * Add a block to output.
     *
     * @param string $blockName
     * @param string $method
     */
    public function addOutputBlock($blockName, $method = 'toHtml')
    {
        $this->_output[$blockName] = [$blockName, $method];

        return $this;
    }

    public function removeOutputBlock($blockName)
    {
        unset($this->_output[$blockName]);

        return $this;
    }

    /**
     * Get all blocks marked for output.
     *
     * @return string
     */
    public function getOutput()
    {
        $out = '';
        if (!empty($this->_output)) {
            foreach ($this->_output as $callback) {
                $out .= $this->getBlock($callback[0])->$callback[1]();
            }
        }

        return $out;
    }

    public function setXml(Element $node)
    {
        $this->_xml = $node;

        return $this;
    }

    /**
     * Returns node found by the $path.
     *
     * @see     \Layout\Layout\Element::descend
     *
     * @param string $path
     *
     * @return \Layout\Layout\Element
     */
    public function getNode($path = null)
    {
        if (!$this->_xml instanceof \Layout\Layout\Element) {
            return false;
        } elseif ($path === null) {
            return $this->_xml;
        } else {
            return $this->_xml->descend($path);
        }
    }
    /**
     * Returns nodes found by xpath expression.
     *
     * @param string $xpath
     *
     * @return array
     */
    public function getXpath($xpath)
    {
        if (empty($this->_xml)) {
            return false;
        }
        if (!$result = @$this->_xml->xpath($xpath)) {
            return false;
        }

        return $result;
    }

    /**
     * Return Xml of node as string.
     *
     * @return string
     */
    public function getXmlString()
    {
        return $this->getNode()->asNiceXml('', false);
    }

    /**
     * Imports XML file.
     *
     * @param string $filePath
     *
     * @return boolean
     */
    public function loadFile($filePath)
    {
        if (!is_readable($filePath)) {
            //throw new Exception('Can not read xml file '.$filePath);
            return false;
        }
        $fileData = file_get_contents($filePath);
        $fileData = $this->processFileData($fileData);

        return $this->loadString($fileData, $this->_elementClass);
    }
    /**
     * Imports XML string.
     *
     * @param string $string
     *
     * @return boolean
     */
    public function loadString($string)
    {
        if (is_string($string)) {
            $xml = simplexml_load_string($string, $this->_elementClass);
            if ($xml instanceof \Layout\Layout\Element) {
                $this->_xml = $xml;

                return true;
            }
        } else {
            \Log::exception(new Exception('"$string" parameter for simplexml_load_string is not a string'));
        }

        return false;
    }
    /**
     * Imports DOM node.
     *
     * @param DOMNode $dom
     *
     * @return \Layout\Layout\Element
     */
    public function loadDom($dom)
    {
        $xml = simplexml_import_dom($dom, $this->_elementClass);
        if ($xml) {
            $this->_xml = $xml;

            return true;
        }

        return false;
    }
    /**
     * Create node by $path and set its value.
     *
     * @param string  $path      separated by slashes
     * @param string  $value
     * @param boolean $overwrite
     *
     * @return \Layout\Layout\Element
     */
    public function setNode($path, $value, $overwrite = true)
    {
        $xml = $this->_xml->setNode($path, $value, $overwrite);

        return $this;
    }

    /**
     * Enter description here...
     *
     * @param \Layout\Layout\Element $config
     * @param boolean             $overwrite
     *
     * @return \Layout\Layout\Element
     */
    public function extend(\Layout\Layout\Element $config, $overwrite = true)
    {
        $this->getNode()->extend($config->getNode(), $overwrite);

        return $this;
    }
}
