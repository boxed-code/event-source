<?php

namespace BoxedCode\EventSource\Consumer;

class Consumer
{
    protected $http;

    protected $listeners = [
        '*' => [],
    ];

    public function __construct($http)
    {
        $this->http = $http;

        EventSourceStream::register();
    }

    public function listen($event, $callback)
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $callback;

        return $this;
    }

    public function emit($event, $data = [], $id = null)
    {
        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $listener) {
                call_user_func_array($listener, [$event, $data, $id]);
            }
        }

        foreach ($this->listeners['*'] as $listener) {
            call_user_func_array($listener, [$event, $data, $id]);
        }
    }

    public function consume($uri, $options = [], $method = 'GET')
    {        
        $context = stream_context_create([
            'event-source' => [
                'handler' => [$this, 'emit']
            ]
        ]);

        $stream = fopen("event-source://default", "r+", false, $context);

        $response = $this->http->request($method, $uri);

        fclose($stream);

        return $response;
    }
}