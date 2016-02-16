<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * Description of Stream
 */
class StringStream implements \Psr\Http\Message\StreamInterface
{
    
    private $string;
    
    private $length;
    
    private $offset = 0;
    
    public function __construct($string)
    {
        $this->string = $string;
        $this->length = strlen($string);
    }
    
    public function __toString()
    {
        $this->offset = $this->length;
        return $this->string;
    }

    public function close()
    {
        $this->detach();
    }

    public function detach()
    {
        unset($this->string);
        return null;
    }

    public function eof()
    {
        return $this->offset >= $this->length;
    }

    public function getContents()
    {
        return $this->read($this->length - $this->offset);
    }

    public function getMetadata($key = null)
    {
        if (isset($key)) {
            return null;
        }
        return [];
    }

    public function getSize()
    {
        return $this->length;
    }

    public function isReadable()
    {
        return isset($this->string);
    }

    public function isSeekable()
    {
        return isset($this->string);
    }

    public function isWritable()
    {
        return isset($this->string);
    }

    public function read($length)
    {
        $data = substr($this->string, $this->offset, $length);
        $this->offset += strlen($data);
        return $data;
    }

    public function rewind()
    {
        $this->offset = 0;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        switch ($whence) {
            case SEEK_SET:
                $this->offset = $offset;
                break;
            case SEEK_CUR:
                $this->offset += $offset;
                break;
            case SEEK_END:
                $this->offset = $this->length + $offset;
                break;
        }
    }

    public function tell()
    {
        return $this->offset;
    }

    public function write($string)
    {
        $length = strlen($string);
        $this->string = substr_replace($this->string, $string, $this->offset, $length);
        $this->offset += $length;
        $this->length = strlen($this->string);
        return $length;
    }
}
