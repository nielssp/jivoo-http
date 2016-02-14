<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * Description of Stream
 */
class PhpStream implements \Psr\Http\Message\StreamInterface
{
    
    private $stream;
    
    public function __construct($stream)
    {
        $this->stream = $stream;
    }
    
    public function __toString()
    {
        $this->rewind();
        return $this->getContents();
    }

    public function close()
    {
        fclose($this->stream);
    }

    public function detach()
    {
        $stream = $this->stream;
        unset($this->stream);
        return $stream;
    }

    public function eof()
    {
        return feof($this->stream);
    }

    public function getContents()
    {
        return stream_get_contents($this->stream);
    }

    public function getMetadata($key = null)
    {
        $metadata = stream_get_meta_data($this->stream);
        if (isset($key)) {
            return isset($metadata[$key]) ? $metadata[$key] : null;
        }
        return $metadata;
    }

    public function getSize()
    {
        return null;
    }

    public function isReadable()
    {
        return in_array(
            $this->getMetadata('mode'),
            ['r', 'r+', 'w+', 'a+', 'x+', 'c+']
        );
    }

    public function isSeekable()
    {
        return $this->getMetadata('seekable');
    }

    public function isWritable()
    {
        return in_array(
            $this->getMetadata('mode'),
            ['w', 'r+', 'w+', 'c', 'a', 'x', 'a+', 'x+', 'c+']
        );
    }

    public function read($length)
    {
        return fread($this->stream, $length);
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        fseek($this->stream, $offset, $whence);
    }

    public function tell()
    {
        return ftell($this->stream);
    }

    public function write($string)
    {
        return fwrite($this->stream, $string);
    }
}
