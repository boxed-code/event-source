<?php

namespace BoxedCode\EventSource\Emitter\Buffers;

use Monolog\Logger;

class MonologBuffer implements Buffer
{
    protected $logger;

    protected $level;

    protected $buffer = '';

    protected $isOutputting = false;

    public function __construct(Logger $logger, $level = Logger::DEBUG)
    {
        $this->logger = $logger;

        $this->level = $level;
    }

    public function setOutputting($bool)
    {
        $this->isOutputting = $bool;
    }

    public function write($string)
    {
        $this->buffer .= $string;
    }

    public function flush()
    {
        $this->logger->addRecord(
            $this->level, $this->buffer
        );

        $this->buffer = '';
    }

    public function end()
    {
        //
    }
}