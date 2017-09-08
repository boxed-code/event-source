<?php

namespace BoxedCode\EventSource\Emitter\Formatters;

use BoxedCode\EventSource\Emitter\Emitter;

class JsonFormatter implements Formatter
{
    public function format($message, $event, $id)
    {
        if (is_string($message)) {
            $message = ['message' => $message];
        }

        return json_encode($message);
    }
}