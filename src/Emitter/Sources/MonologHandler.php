<?php

namespace BoxedCode\EventSource\Emitter\Sources;

use BoxedCode\EventSource\Emitter\EventEmitter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class MonologHandler extends AbstractProcessingHandler
{
    protected $emitter;

    protected $level;

    public function __construct(EventEmitter $emitter, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->emitter = $emitter;

        $this->level = $level;
    }

    protected function write(array $record)
    {
        $this->emitter->raw($record['formatted']);
    } 

    /**
     * Gets the default formatter.
     *
     * @return FormatterInterface
     */
    protected function getDefaultFormatter()
    {
        return new JsonFormatter();
    }
}