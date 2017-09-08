<?php

namespace BoxedCode\EventSource\Emitter\Buffers;

interface Buffer
{
    public function setOutputting($bool);
    public function write($string);
    public function flush();
    public function end();
}