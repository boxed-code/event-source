<?php

namespace BoxedCode\EventSource\Emitter;

use BoxedCode\EventSource\Emitter\EventEmitter;
use Closure;
use Exception;

class Syndicator implements EventEmitter
{
    protected $eventSources = [];

    protected $callback;

    public function __construct()
    {
        $this->setEmitters(func_get_args());
    }

    public function raw($message, $event = null, $id = null)
    {
        $this->syndicateCall('raw', func_get_args());
    }

    public function push($message, $event = null, $id = null)
    {
        $this->syndicateCall('push', func_get_args());
    }

    public function message($message, $data = [], $event = null, $id = null)
    {
        $this->syndicateCall('message', func_get_args());
    }

    public function setEmitters(array $sources)
    {
        $this->eventSources = $sources;

        return $this;
    }

    public function setCallback(Closure $callback)
    {
        $this->callback = $callback;

        $callbackClosure = $this->getCallbackClosure();

        $proxyClosure = function() use ($callbackClosure) {
            return $callbackClosure();
        }; 
        
        $this->syndicateCall('setCallback', [$proxyClosure]);

        return $this;
    }

    public function getCallbackClosure()
    {
        return function() {
            $callback = $this->callback;

           return $callback($this);
        };
    }

    public function send()
    {
        throw new Exception('Not supported.');
    }

    protected function syndicateCall($name, $args)
    {
        foreach ($this->eventSources as $eventSource) {
            call_user_func_array([$eventSource, $name], $args);
        }
    }

    public function __call($name, $args)
    {
        $this->syndicateCall($name, $args);
    }
}