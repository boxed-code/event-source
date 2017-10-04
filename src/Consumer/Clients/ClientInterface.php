<?php

namespace BoxedCode\EventSource\Consumer\Clients;

interface ClientInterface
{
    public function request($method, $uri, $stream, $options = []);
}