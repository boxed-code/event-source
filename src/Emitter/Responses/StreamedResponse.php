<?php

namespace BoxedCode\EventSource\Emitter\Responses;

use BoxedCode\EventSource\Emitter\Buffers\Buffer;
use Closure;

class StreamedResponse implements Response
{
    public function __construct()
    {
        ignore_user_abort(false);
    }
    
    public function output(Closure $renderer, Buffer $buffer)
    {
        header("Content-Type: text/event-stream\n\n");

        $buffer->setOutputting(true);

        $buffer->flush();

        $renderer();

        $buffer->end();

        exit;
    }
}