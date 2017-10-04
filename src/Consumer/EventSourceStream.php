<?php

namespace BoxedCode\EventSource\Consumer;

class EventSourceStream
{
    protected $handler;

    protected $buffer = '';

    protected $lineBuffer = [];

    protected $position = 0;

    protected $length = 0;

    public static function register()
    {
        $wrappers = stream_get_wrappers();

        if (!in_array('event-source', $wrappers)) {
            if (!stream_wrapper_register("event-source", static::class)) {
                throw new Exception('Failed to register wrapper');
            }
        }
    }

    public function stream_eof()
    {
        return false;
    }

    public function stream_open($path, $mode, $options, &$opened_path) 
    {
        $options = stream_context_get_options($this->context);

        if (isset($options['event-source']) && isset($options['event-source']['handler'])) {
            $this->handler = $options['event-source']['handler'];
        }

        return true;
    }

    public function stream_write($data) 
    {
        $lines = explode("\n", $data);

        // Get the previous partial line from the buffer 
        // and append it to the first of our 'new' lines.
        $lines[0] = $this->buffer . $lines[0];

        // Add the last line to the buffer as it 
        // should be incomplete or an empty line.
        $lc = count($lines);

        $this->buffer = $lines[$lc-1];

        unset($lines[$lc-1]);

        $this->processLines($lines);

        $this->position =+ strlen($data);

        $this->length =+ strlen($data);

        return strlen($data);
    }

    public function stream_seek($offset, $whence = SEEK_SET)
    {
        switch ($whence)
        {
            case SEEK_SET:
                $this->position = $offset;
            case SEEK_CUR:
                $this->position =+ $offset;
            case SEEK_END:
                $this->position = $this->length + $offset;
        }

        return true;
    }

    public function stream_tell()
    {
        return $this->position;
    }

    protected function processLines(array $lines)
    {
        $this->lineBuffer = array_merge(
            $this->lineBuffer, $lines
        );

        if (count($this->lineBuffer) > 0) {
            list($line) = array_slice($this->lineBuffer, -1);
        } else {
            $line = '';
        }

        // Try and process the message.
        if (trim($line) === '') {
            $message = implode(PHP_EOL, $this->lineBuffer);

            // Extract the id, event & data lines.
            preg_match_all('/(\w+):\s(.+)/i', $message, $matches, PREG_SET_ORDER);

            $message = [];

            foreach ($matches as $group) {
                $message[$group[1]] = $group[2];
            }

            if (isset($message['data'])) {
                $this->lineBuffer = [];
                $previousLine = '';

                $id = isset($message['id']) ? $message['id'] : null;
                $event = isset($message['event']) ? $message['event'] : 'message';

                if (is_callable($this->handler)) {
                    \Log::debug($message);
                    call_user_func_array($this->handler, [$event, $message['data'], $id]);
                }
            }
        }
    }
}