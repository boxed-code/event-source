<?php

namespace BoxedCode\EventSource\Consumer\Clients;

class GuzzleClient implements ClientInterface
{
    protected $instance;

    public function __construct(\GuzzleHttp\Client $client)
    {
        $this->instance = $client;
    }

    public function request($method, $uri, $stream)
    {
        $options = array_merge(
            $options, ['stream' => true, 'sink' => $stream]
        );

        return $this->instance->request($method, $uri, $options);
    }
}