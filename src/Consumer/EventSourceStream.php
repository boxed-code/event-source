<?php

namespace BoxedCode\EventSource\Consumer;

class EventSourceStream
{
    protected $handler;

    protected $data;

    protected $buffer = '';

    protected $lineBuffer = [];

    protected $messageBuffer = [];

    protected $position = 0;

    protected $length = 0;

    protected $lastRead;

    protected $lastWrite;

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
        return $this->position >= $this->length;
    }

    public function stream_open($path, $mode, $options, &$opened_path) 
    {
        $options = stream_context_get_options($this->context);

        if (isset($options['event-source']) && isset($options['event-source']['handler'])) {
            $this->handler = $options['event-source']['handler'];
        }

        $this->lastRead = $this->lastWrite = time();

        return true;
    }

    public function stream_read($count)
    {
        $result = @substr($this->data, $this->position, $count);

        $this->position += strlen($result);

        return $result;
    }

    public function stream_write($data) 
    {
        $this->data .= $data;

        $lines = explode("\n", $data);

        // Get the previous partial line from the buffer 
        // and append it to the first of our 'new' lines.
        $lines[0] = $this->buffer . $lines[0];

        // Add the last line to the buffer as it 
        // should be incomplete or an empty line.
        $lc = count($lines);

        $this->buffer = $lines[$lc-1];

        unset($lines[$lc-1]);

        $writeLength = strlen($data);

        $this->position += $writeLength;

        $this->length += $writeLength;

        $this->lastWrite = time();

        $this->processLines($lines);

        return $writeLength;
    }

    public function stream_stat()
    {
        return [
            'dev' => 0,
            'ino' => 0,
            'mode' => 0,
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => -1,
            'size' => $this->length,
            'atime' => time(),
            'mtime' => time(),
            'ctime' => time(),
            'blksize' => -1,
            'blocks' => -1,
        ];
    }

    public function stream_seek($offset, $whence = SEEK_SET)
    {
        switch ($whence)
        {
            case SEEK_SET:
                $this->position = $offset;
                break;
            case SEEK_CUR:
                $this->position += $offset;
                break;
            case SEEK_END:
                $this->position = $this->length + $offset;
                break;
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

        $messages = [];
        $buffer = $this->messageBuffer;
        $cursor = 0;

        foreach ($this->lineBuffer as $k => $line) {
            $line = trim($line);

            if (empty($line)) {
                // Clear the buffer.
                if (isset($buffer['data'])) {
                    $messages[] = $buffer;
                }
                
                $buffer = [];

                continue;
            }

            if (preg_match('/(id|data|event):\s?(.+)/i', $line, $matches)) {
                $buffer[$matches[1]] = $matches[2];
            }
        }

        $this->lineBuffer = [];

        $this->messageBuffer = $buffer;

        foreach ($messages as $message) {
            $id = isset($message['id']) ? $message['id'] : null;
            $event = isset($message['event']) ? $message['event'] : 'message';

            if (is_callable($this->handler)) {
                call_user_func_array(
                    $this->handler, [$event, $message['data'], $id]
                );
            }
        }
    }
}