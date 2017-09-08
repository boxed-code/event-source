<?php

namespace BoxedCode\EventSource\Emitter\Buffers;

class StdOutBuffer implements Buffer
{
    protected $isOutputting = false;

    public function __construct()
    {
        ob_start();
    }

    public function setOutputting($bool)
    {
        $this->isOutputting = $bool;
    }

    public function write($string)
    {
        echo $string;
    }

    public function flush()
    {
        if ($this->isOutputting) {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            flush();

            ob_start();
        }
    }

    public function end()
    {
        ob_end_flush();

        flush();
    }
}