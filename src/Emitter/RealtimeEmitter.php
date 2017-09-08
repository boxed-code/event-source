<?php

namespace BoxedCode\EventSource\Emitter;

use BoxedCode\EventSource\Emitter\Buffers\Buffer;
use BoxedCode\EventSource\Emitter\Buffers\StdOutBuffer;
use BoxedCode\EventSource\Emitter\EventEmitter;
use BoxedCode\EventSource\Emitter\Formatters\Formatter;
use BoxedCode\EventSource\Emitter\Responses\Response;
use BoxedCode\EventSource\Emitter\Responses\StreamedResponse;

class RealtimeEmitter extends Emitter implements EventEmitter
{
    public function __construct(Response $response = null, Buffer $buffer = null)
    {
        if (!$buffer) {
            $buffer = new StdOutBuffer();
        }

        if (!$response) {
            $response = new StreamedResponse();
        }

        parent::__construct($response, $buffer);
    }
}