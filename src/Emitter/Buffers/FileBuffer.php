<?php

namespace BoxedCode\EventSource\Emitter\Buffers;

class FileBuffer implements Buffer
{
    protected $isOutputting = false;

    protected $cacheFilePath;

    public function __construct($cacheFilePath = null)
    {
        ob_start();

        $this->cacheFilePath = $cacheFilePath;

        if ($cacheFilePath && !file_exists($this->cacheFilePath)) {
            file_put_contents($this->cacheFilePath, '');
        }
    }

    public function getSourceFilePath()
    {
        return $this->cacheFilePath;
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
        if ($data = ob_get_contents()) {
            ob_clean();
            
            $this->writeFile($data);
        }
    }

    protected function writeFile($data)
    {
        $f = fopen($this->cacheFilePath, 'a+');
        
        fwrite($f, $data);

        fclose($f);   
    }

    public function end()
    {
        ob_end_clean();
    }
}