<?php

namespace BoxedCode\EventSource\Emitter\Formatters;

interface Formatter {
    public function format($data, $event, $id);
}