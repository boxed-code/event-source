<?php

namespace BoxedCode\EventSource\Emitter;

use BoxedCode\EventSource\Emitter\Buffers\Buffer;
use BoxedCode\EventSource\Emitter\EventEmitter;
use BoxedCode\EventSource\Emitter\Formatters\Formatter;
use BoxedCode\EventSource\Emitter\Handlers\Handler;
use BoxedCode\EventSource\Emitter\Responses\Response;
use Closure;

class Emitter implements EventEmitter
{
    protected $formatter;

    protected $callback;

    protected $buffer;

    protected $response;

    public function __construct(Response $response, Buffer $buffer)
    {
        $this->response = $response;
        
        $this->buffer = $buffer;
    }

    public function getBuffer()
    {
        return $this->buffer;
    }

    public function setBuffer(Buffer $buffer)
    {
        $this->buffer = $buffer;

        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }

    public function getFormatter()
    {
        return $this->formatter;
    }

    public function setFormatter(Formatter $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function setCallback(Closure $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    public function getCallbackClosure()
    {
        return function() {
            if (!($callback = $this->callback)) {
                $callback = function() { /* Do Nothing */ };
            }

           return $callback($this);
        };
    }

    protected function getFormattedMessage($message, $event, $id)
    {
        if ($this->formatter instanceof Formatter) {
            return $this->formatter->format(
                $message, $event, $id
            );
        } elseif (is_callable($this->formatter)) {
            return call_user_func_array(
                $this->formatter, [$message, $event, $id]
            );
        }

        return $message;
    }

    public function raw($data, $event = null, $id = null)
    {
        if ($event) {
            $this->buffer->write("event: " . $event . "\n");
        }

        if ($id) {
            $this->buffer->write("id: " . $id . "\n");
        }

        $this->buffer->write("data: " . $data . "\n\n");

        $this->buffer->flush();
    }

    public function push($message, $event = null, $id = null)
    {
        $message = $this->getFormattedMessage(
            $message, $event, $id
        );

        $this->raw($message, $event, $id);
    }

    public function message($message, $data = [], $event = null, $id = null)
    {
        $this->push(['message' => $message, 'data' => $data], $event, $id);
    }

    public function send()
    {
        return $this->response->output(
            $this->getCallbackClosure(), 
            $this->buffer
        );
    }

    public function __call($name, $args)
    {
        if (is_object($this->formatter) && method_exists($this->formatter, $name)) {
            call_user_func_array([$this->formatter, $name], $args);
        }
    }
}