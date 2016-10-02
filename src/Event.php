<?php

namespace Layout;

use Layout\Core\Contracts\EventsDispatcher;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;

class Event implements EventsDispatcher
{
	protected $_event;

	public function __construct(DispatcherContract $event)
	{	
		$this->_event = $event;	
	}

	public function listen($event, $listener, $priority = 0)
	{
		return $this->_event->listen($event, $listener, $priority);
	}

	public function fire($event, array $payload = [])
	{
		return $this->_event->fire($event, $payload);
	}

	/**
     * Magically handle calls to certain methods on the event factory.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \ErrorException
     *
     */
    public function __call($method, $parameters)
    {
    	return call_user_func_array([$this->_event, $method], $parameters);
    }
}