<?php

namespace BoxedCode\EventSource\Emitter\Responses;

use BoxedCode\EventSource\Emitter\Buffers\Buffer;
use Closure;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyResponse;

class SymfonyStreamedResponse implements Response
{
    protected $response;

    public function __construct(SymfonyResponse $response = null)
    {
        if (!$response) {
            $response =  new SymfonyResponse();
        }

        $this->response = $response;
    }

    public function output(Closure $renderer, Buffer $buffer)
    {
        $this->response->setCallback(function() use ($renderer, $buffer) {
            $buffer->setOutputting(true);

            $buffer->flush();

            $renderer();

            $buffer->end();
        });

        $this->response->headers->set('Content-Type', 'text/event-stream');

        return $this->response;
    }
}