<?php

namespace Layout;

use Layout\Core\Contracts\ConfigResolver;
use Layout\Core\Exceptions\InvalidRouterHandleException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class Config implements ConfigResolver
{
    protected $_config;

    public function __construct(ConfigRepository $config)
    {
        $this->_config = $config;
    }

    public function get($key, $default = null)
    {
        if ($key == 'current_route_handle') {
            return $this->getCurrentRoute();
        }
        if ($key == 'handle_layout_section') {
            return $this->getCurrentLayoutSection();
        }
        return $this->_config->get('layout.'.$key, $default);
    }

    public function getCurrentRoute()
    {
        $routerHandler = $this->_config->get('layout.handle_layout_route');
        $routerHandler = call_user_func($routerHandler);
        if (empty($routerHandler) || is_null($routerHandler)) {
            if ($this->_config->get('layout.strict', false)) {
                throw new InvalidRouterHandleException('Invalid Router Handle supplied');
            }
        }
        return $routerHandler;
    }

    public function getCurrentLayoutSection()
    {
        $section = $this->_config->get('layout.handle_layout_section');
        $section = call_user_func($section);
        if (empty($section) || is_null($section)) {
           return 'default';
        }
        return $section;
    }

    /**
     * Magically handle calls to certain methods on the config factory.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \ErrorException
     *
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->_config, $method], $parameters);
    }
}
