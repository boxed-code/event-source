<?php

namespace BoxedCode\EventSource\Emitter;

interface EventEmitter {
    public function raw($message, $event = null, $id = null);
    public function push($message, $event = null, $id = null);
    public function message($message, $data = [], $event = null, $id = null);
}