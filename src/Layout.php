<?php

namespace Layout;

use Layout\Exceptions\InvalidBlockException;
use Layout\Exceptions\MethodNotFoundException;
use Layout\Layout\Element;
use ReflectionFunctionAbstract;
use ReflectionMethod;

class Layout
{
    /**
     * layout xml.
     *
     * @var \Layout\Layout\Element
     */
    protected $xmlTree = null;

    /**
     * Class name of simplexml elements for this configuration.
     *
     * @var string
     */
    protected $elementClass;

    /**
     * Layout Update module.
     *
     * @var \Layout\Layout\Update
     */
    protected $update;

    /**
     * Blocks registry.
     *
     * @var array
     */
    protected $blocks = [];

    /**
     * Cache of block callbacks to output during rendering.
     *
     * @var array
     */
    protected $output = [];

    /**
     * Event Instance.
     *
     * @var Illuminate\Events\Dispatcher
     */
    protected $events;

    public function __construct(\Illuminate\Foundation\Application $app)
    {
        $this->elementClass = Element::class;
        $this->setXml(simplexml_load_string('<layout/>', $this->elementClass));
        $this->events = $app['events'];
        $this->update = $app['render.layout.update'];
    }

    /**
     * Layout update instance.
     *
     * @return \Layout\Layout\Update
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * Layout xml generation.
     *
     * @return \Layout\Layout
     */
    public function generateXml()
    {
        $xml = $this->getUpdate()->asSimplexml();

        $removeInstructions = $xml->xpath('//remove');
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
        if (isset($node['ifconfig']) && ($configPath = (string) $node['ifconfig'])) {
            if (config($configPath, false)) {
                return $this;
            }
        }

        $className = (string) $node['class'];
        $blockName = (string) $node['name'];
        $profilerKey = 'BLOCK: '.$blockName;

        start_profile($profilerKey);

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

        stop_profile($profilerKey);

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
        if (isset($node['ifconfig']) && ($configPath = (string) $node['ifconfig'])) {
            if (!config($configPath, false)) {
                return $this;
            }
        }

        if (isset($node['ifcond'])) {
            if (!$this->runHelper((string) $node['ifcond'])) {
                return $this;
            }
        }

        $method = (string) $node['method'];
        if (!empty($node['block'])) {
            $parentName = (string) $node['block'];
        } else {
            $parentName = $parent->getBlockName();
        }

        $profilerKey = 'BLOCK ACTION: '.$parentName.' -> '.$method;

        start_profile($profilerKey);

        if (!empty($parentName)) {
            $block = $this->getBlock($parentName);
        }
        if (!empty($block)) {
            $args = (array) $node->children();
            unset($args['@attributes']);

            foreach ($args as $key => $arg) {
                if (($arg instanceof \Layout\Layout\Element)) {
                    if (isset($arg['helper'])) {
                        $args[$key] = $this->runHelper((string) $arg['helper']);
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

        stop_profile($profilerKey);

        return $this;
    }

    /**
     * @param String $helper
     *
     * @return mixed
     */
    protected function runHelper($helper)
    {
        list($class, $method) = explode('@', $helper);

        $parameters = $this->resolveClassMethodDependencies(
            [], $class, $method
        );

        if (!method_exists($instance = app($class), $method)) {
            throw new MethodNotFoundException();
        }

        return call_user_func_array([$instance, $method], $parameters);
    }

    /**
     * Translate layout node.
     *
     * @param \Layout\Layout\Element $node
     * @param array                  $args
     **/
    protected function _translateLayoutNode($node, &$args)
    {
        if (isset($node['translate'])) {
            // Handle translations in arrays if needed
            $translatableArguments = explode(' ', (string) $node['translate']);
            foreach ($translatableArguments as $translatableArgumentName) {
                $args[$translatableArgumentName] = trans($args[$translatableArgumentName]);
            }
        }
    }

    /**
     * Save block in blocks registry.
     *
     * @param string         $name
     * @param \Layout\Layout $block
     */
    public function setBlock($name, $block)
    {
        $this->blocks[$name] = $block;

        return $this;
    }

    /**
     * Remove block from registry.
     *
     * @param string $name
     */
    public function unsetBlock($name)
    {
        $this->blocks[$name] = null;
        unset($this->blocks[$name]);

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
            $name = 'ANONYMOUS_'.sizeof($this->blocks);
        }

        $block->setClass($class);
        $block->setNameInLayout($name);
        $block->addData($attributes);
        $block->setLayout($this);

        $this->blocks[$name] = $block;

        $this->events->fire('layout.block.create.after', ['block' => $block]);

        return $this->blocks[$name];
    }

    /**
     * Add a block to registry, create new object if needed.
     *
     * @param string|\Layout\Block $blockClass
     * @param string               $blockName
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
        return $this->blocks;
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
        if (isset($this->blocks[$name])) {
            return $this->blocks[$name];
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
        $this->output[$blockName] = [$blockName, $method];

        return $this;
    }

    public function removeOutputBlock($blockName)
    {
        unset($this->output[$blockName]);

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
        if (!empty($this->output)) {
            foreach ($this->output as $callback) {
                $out .= call_user_func_array([$this->getBlock($callback[0]), $callback[1]], []);
            }
        }

        return $out;
    }

    public function setXml(Element $node)
    {
        $this->xmlTree = $node;

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
        if (!$this->xmlTree instanceof \Layout\Layout\Element) {
            return false;
        } elseif ($path === null) {
            return $this->xmlTree;
        } else {
            return $this->xmlTree->descend($path);
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
        if (empty($this->xmlTree)) {
            return false;
        }
        if (!$result = @$this->xmlTree->xpath($xpath)) {
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
     * @return bool
     */
    public function loadFile($filePath)
    {
        if (!is_readable($filePath)) {
            //throw new Exception('Can not read xml file '.$filePath);
            return false;
        }
        $fileData = file_get_contents($filePath);
        $fileData = $this->processFileData($fileData);

        return $this->loadString($fileData, $this->elementClass);
    }
    /**
     * Imports XML string.
     *
     * @param string $string
     *
     * @return bool
     */
    public function loadString($string)
    {
        if (is_string($string)) {
            $xml = simplexml_load_string($string, $this->elementClass);
            if ($xml instanceof \Layout\Layout\Element) {
                $this->xmlTree = $xml;

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
        $xml = simplexml_import_dom($dom, $this->elementClass);
        if ($xml) {
            $this->xmlTree = $xml;

            return true;
        }

        return false;
    }
    /**
     * Create node by $path and set its value.
     *
     * @param string $path      separated by slashes
     * @param string $value
     * @param bool   $overwrite
     *
     * @return \Layout\Layout\Element
     */
    public function setNode($path, $value, $overwrite = true)
    {
        $xml = $this->xmlTree->setNode($path, $value, $overwrite);

        return $this;
    }

    /**
     * Enter description here...
     *
     * @param \Layout\Layout\Element $config
     * @param bool                   $overwrite
     *
     * @return \Layout\Layout\Element
     */
    public function extend(\Layout\Layout\Element $config, $overwrite = true)
    {
        $this->getNode()->extend($config->getNode(), $overwrite);

        return $this;
    }

    /**
     * Call a class method with the resolved dependencies.
     *
     * @param object $instance
     * @param string $method
     *
     * @return mixed
     */
    protected function callWithDependencies($instance, $method)
    {
        return call_user_func_array(
            [$instance, $method], $this->resolveClassMethodDependencies([], $instance, $method)
        );
    }

    /**
     * Resolve the object method's type-hinted dependencies.
     *
     * @param array  $parameters
     * @param object $instance
     * @param string $method
     *
     * @return array
     */
    protected function resolveClassMethodDependencies(array $parameters, $instance, $method)
    {
        if (!method_exists($instance, $method)) {
            return $parameters;
        }

        return $this->resolveMethodDependencies(
            $parameters, new ReflectionMethod($instance, $method)
        );
    }

    /**
     * Resolve the given method's type-hinted dependencies.
     *
     * @param array                       $parameters
     * @param \ReflectionFunctionAbstract $reflector
     *
     * @return array
     */
    public function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflector)
    {
        foreach ($reflector->getParameters() as $key => $parameter) {
            // If the parameter has a type-hinted class, we will check to see if it is already in
            // the list of parameters. If it is we will just skip it as it is probably a model
            // binding and we do not want to mess with those; otherwise, we resolve it here.
            $class = $parameter->getClass();

            if ($class && !$this->alreadyInParameters($class->name, $parameters)) {
                array_splice(
                    $parameters, $key, 0, [$this->container->make($class->name)]
                );
            }
        }

        return $parameters;
    }

    /**
     * Determine if an object of the given class is in a list of parameters.
     *
     * @param string $class
     * @param array  $parameters
     *
     * @return bool
     */
    protected function alreadyInParameters($class, array $parameters)
    {
        return !is_null(array_first($parameters, function ($key, $value) use ($class) {
            return $value instanceof $class;
        }));
    }
}
