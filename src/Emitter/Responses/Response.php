<?php

namespace BoxedCode\EventSource\Emitter\Responses;

use Closure;
use BoxedCode\EventSource\Emitter\Buffers\Buffer;

interface Response
{
    public function output(Closure $renderer, Buffer $buffer);
}