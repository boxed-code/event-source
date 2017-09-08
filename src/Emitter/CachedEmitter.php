<?php

namespace BoxedCode\EventSource\Emitter;

use BoxedCode\EventSource\Emitter\Buffers\Buffer;
use BoxedCode\EventSource\Emitter\Buffers\FileBuffer;
use BoxedCode\EventSource\Emitter\Buffers\StdOutBuffer;
use BoxedCode\EventSource\Emitter\EventEmitter;
use BoxedCode\EventSource\Emitter\Responses\Response;
use BoxedCode\EventSource\Emitter\Responses\StreamedResponse;

class CachedEmitter extends Emitter implements EventEmitter
{
    protected $cacheFilePath;

    protected $outputBuffer;

    public function __construct(
        $cacheFilePath = null, Response $response = null, 
        Buffer $inputBuffer = null, Buffer $outputBuffer = null
    ) {
        $this->cacheFilePath = $cacheFilePath;

        if (!$outputBuffer) {
            $outputBuffer = new StdOutBuffer();
        }

        $this->outputBuffer = $outputBuffer;

        if (!$inputBuffer) {
            $inputBuffer = new FileBuffer($cacheFilePath);
        }

        if (!$response) {
            $response = new StreamedResponse();
        }

        parent::__construct($response, $inputBuffer);
    }

    public function getSourceFilePath()
    {
        return $this->cacheFilePath;
    }

    public function send()
    {
        return $this->response->output(
            $this->getCallbackClosure(), 
            $this->outputBuffer
        );
    }

    public function getCallbackClosure()
    {
        return function() {
            $currentPosition = 0;

            while (true) {
                $f = fopen($this->cacheFilePath, 'r');

                fseek($f, $currentPosition);

                if ($data = fgets($f)) {
                    $currentPosition = ftell($f);

                    $this->outputBuffer->write($data);

                    fclose($f);

                    usleep(500);
                } else {
                    $this->push('{}', 'ping');

                    fclose($f);

                    sleep(1);
                }
                
                $this->outputBuffer->flush();
            }
        };
    }
}