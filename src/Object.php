<?php

namespace Layout;

use Exception;

class Object implements \ArrayAccess
{
    /**
     * Object attributes.
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Setter/Getter underscore transformation cache.
     *
     * @var array
     */
    protected static $_underscoreCache = [];

    public function __construct()
    {
        $this->_construct();
    }

    /**
     * Internal constructor, that is called from real constructor.
     */
    protected function _construct()
    {
    }

    /**
     * Add data to the object.
     *
     * Retains previous data in the object.
     *
     * @param array $arr
     *
     * @return \Layout\Object
     */
    public function addData(array $arr)
    {
        foreach ($arr as $index => $value) {
            $this->setData($index, $value);
        }

        return $this;
    }

    /**
     * Overwrite data in the object.
     *
     * $key can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return \Layout\Object
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $this->_data = $key;
        } else {
            $this->_data[$key] = $value;
        }

        return $this;
    }

    /**
     * Unset data from the object.
     *
     * $key can be a string only. Array will be ignored.
     *
     * @param string $key
     *
     * @return \Layout\Object
     */
    public function unsetData($key = null)
    {
        if (is_null($key)) {
            $this->_data = [];
        } else {
            unset($this->_data[$key]);
        }

        return $this;
    }

    /**
     * Retrieves data from the object.
     *
     * If $key is empty will return all the data as an array
     * Otherwise it will return value of the attribute specified by $key
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member.
     *
     * @param string     $key
     * @param string|int $index
     *
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        if ('' === $key) {
            return $this->_data;
        }
        $default = null;
        // accept a/b/c as ['a']['b']['c']
        if (strpos($key, '/')) {
            $keyArr = explode('/', $key);
            $data = $this->_data;
            foreach ($keyArr as $i => $k) {
                if ($k === '') {
                    return $default;
                }
                if (is_array($data)) {
                    if (!isset($data[$k])) {
                        return $default;
                    }
                    $data = $data[$k];
                } elseif ($data instanceof self) {
                    $data = $data->getData($k);
                } else {
                    return $default;
                }
            }

            return $data;
        }
        // legacy functionality for $index
        if (isset($this->_data[$key])) {
            if (is_null($index)) {
                return $this->_data[$key];
            }
            $value = $this->_data[$key];
            if (is_array($value)) {
                if (isset($value[$index])) {
                    return $value[$index];
                }

                return;
            } elseif (is_string($value)) {
                $arr = explode("\n", $value);

                return (isset($arr[$index]) && (!empty($arr[$index]) || strlen($arr[$index]) > 0))
                    ? $arr[$index] : null;
            } elseif ($value instanceof self) {
                return $value->getData($index);
            }

            return $default;
        }

        return $default;
    }

    /**
     * Get value from _data array without parse key.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function _getData($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * If $key is empty, checks whether there's any data in the object
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasData($key = '')
    {
        if (empty($key) || !is_string($key)) {
            return !empty($this->_data);
        }

        return array_key_exists($key, $this->_data);
    }

    /**
     * Convert object attributes to array.
     *
     * @param array $arrAttributes array of required attributes
     *
     * @return array
     */
    public function __toArray(array $arrAttributes = [])
    {
        if (empty($arrAttributes)) {
            return $this->_data;
        }
        $arrRes = [];
        foreach ($arrAttributes as $attribute) {
            if (isset($this->_data[$attribute])) {
                $arrRes[$attribute] = $this->_data[$attribute];
            } else {
                $arrRes[$attribute] = null;
            }
        }

        return $arrRes;
    }
    /**
     * Public wrapper for __toArray.
     *
     * @param array $arrAttributes
     *
     * @return array
     */
    public function toArray(array $arrAttributes = [])
    {
        return $this->__toArray($arrAttributes);
    }

    /**
     * Convert object attributes to JSON.
     *
     * @param array $arrAttributes array of required attributes
     *
     * @return string
     */
    protected function __toJson(array $arrAttributes = [])
    {
        $arrData = $this->toArray($arrAttributes);
        $json = json_encode($arrData);

        return $json;
    }
    /**
     * Public wrapper for __toJson.
     *
     * @param array $arrAttributes
     *
     * @return string
     */
    public function toJson(array $arrAttributes = [])
    {
        return $this->__toJson($arrAttributes);
    }

    /**
     * Set/Get attribute wrapper.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get' :
                $key = $this->_underscore(substr($method, 3));
                $data = $this->getData($key, isset($args[0]) ? $args[0] : null);

                return $data;
            case 'set' :
                $key = $this->_underscore(substr($method, 3));
                $result = $this->setData($key, isset($args[0]) ? $args[0] : null);

                return $result;
            case 'uns' :
                $key = $this->_underscore(substr($method, 3));
                $result = $this->unsetData($key);

                return $result;
            case 'has' :
                $key = $this->_underscore(substr($method, 3));

                return isset($this->_data[$key]);
        }
        //throw new Exception("Invalid method ".get_class($this)."::".$method."(".print_r($args,1).")");
        throw new Exception('Invalid method '.get_class($this).'::'.$method);
    }

    /**
     * Attribute getter (deprecated).
     *
     * @param string $var
     *
     * @return mixed
     */
    public function __get($var)
    {
        $var = $this->_underscore($var);

        return $this->getData($var);
    }
    /**
     * Attribute setter (deprecated).
     *
     * @param string $var
     * @param mixed  $value
     */
    public function __set($var, $value)
    {
        $var = $this->_underscore($var);
        $this->setData($var, $value);
    }

    /**
     * Converts field names for setters and geters.
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unneccessary preg_replace
     *
     * @param string $name
     *
     * @return string
     */
    protected function _underscore($name)
    {
        if (isset(self::$_underscoreCache[$name])) {
            return self::$_underscoreCache[$name];
        }
        $result = strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $name));
        self::$_underscoreCache[$name] = $result;

        return $result;
    }

    protected function _camelize($name)
    {
        return uc_words($name, '');
    }

    /**
     * Implementation of ArrayAccess::offsetSet().
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }
    /**
     * Implementation of ArrayAccess::offsetExists().
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }
    /**
     * Implementation of ArrayAccess::offsetUnset().
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    /**
     * Implementation of ArrayAccess::offsetGet().
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }
}
