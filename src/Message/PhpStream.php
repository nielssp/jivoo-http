<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

use Jivoo\InvalidArgumentException;
use Jivoo\Log\ErrorHandler;
use Psr\Http\Message\StreamInterface;

/**
 * Stream implementation based on PHP streams/resources.
 */
class PhpStream implements StreamInterface
{
    
    /**
     * @var resource
     */
    private $stream;
    
    /**
     * Construct stream.
     *
     * @param string|resource $streamOrPath A resource or path.
     * @param string $mode Stream mode, see {@see fopen}.
     */
    public function __construct($streamOrPath, $mode = 'r')
    {
        if (is_resource($streamOrPath)) {
            $this->stream = $streamOrPath;
        } else {
            \Jivoo\Assume::isString($streamOrPath);
            $error = ErrorHandler::detect(function () use ($streamOrPath, $mode) {
                $this->stream = fopen($streamOrPath, $mode);
            });
            if ($error or $this->stream === false) {
                throw new InvalidArgumentException('Could not open stream', 0, $error);
            }
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (\Jivoo\Http\Message\StreamException $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (! isset($this->stream)) {
            return;
        }
        fclose($this->stream);
        unset($this->stream);
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $stream = $this->stream;
        unset($this->stream);
        return $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        if (! isset($this->stream)) {
            return true;
        }
        return feof($this->stream);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        if (! isset($this->stream)) {
            return '';
        }
        $data = stream_get_contents($this->stream);
        if ($data === false) {
            throw new StreamException('Could not read from stream');
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if (! isset($this->stream)) {
            return isset($key) ? null : [];
        }
        $metadata = stream_get_meta_data($this->stream);
        if (isset($key)) {
            return isset($metadata[$key]) ? $metadata[$key] : null;
        }
        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        if (! isset($this->stream)) {
            return null;
        }
        $stat = fstat($this->stream);
        return $stat['size'];
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        if (! isset($this->stream)) {
            return false;
        }
        return in_array(
            $this->getMetadata('mode'),
            ['r', 'r+', 'w+', 'a+', 'x+', 'c+']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        if (! isset($this->stream)) {
            return false;
        }
        return $this->getMetadata('seekable');
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        if (! isset($this->stream)) {
            return false;
        }
        return in_array(
            $this->getMetadata('mode'),
            ['w', 'r+', 'w+', 'c', 'a', 'x', 'a+', 'x+', 'c+']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        if (! $this->isReadable()) {
            throw new StreamException('Stream not readable');
        }
        $data = fread($this->stream, $length);
        if ($data === false) {
            throw new StreamException('Could not read from stream');
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (! $this->isSeekable()) {
            throw new StreamException('Stream not seekable');
        }
        if (fseek($this->stream, $offset, $whence) !== 0) {
            throw new StreamException('Could not set stream position');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        if (! isset($this->stream)) {
            throw new StreamException('Stream not open');
        }
        $offset = ftell($this->stream);
        if ($offset === false) {
            throw new StreamException('Could not get stream position');
        }
        return $offset;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        if (! $this->isWritable()) {
            throw new StreamException('Stream not writable');
        }
        $bytes = fwrite($this->stream, $string);
        if ($bytes === false) {
            throw new StreamException('Could not write to stream');
        }
        return $bytes;
    }
}
