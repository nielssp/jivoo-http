<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * An uplaoded file.
 * @property-read string $name Client file name.
 * @property-read string $type File MIME type.
 * @property-read int $size Size of file in bytes.
 * @property-read int $error Error code.
 */
class UploadedFile implements \Psr\Http\Message\UploadedFileInterface
{
    
    /**
     * @var string
     */
    private $tmpName;
    
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var string
     */
    private $type;
    
    /**
     * @var int
     */
    private $size;
    
    /**
     * @var int
     */
    private $error;
    
    /**
     * @var PhpStream|null
     */
    private $stream = null;
  
    /**
     * Construct uploaded file.
     *
     * @param array $file File array.
     * @param int $offset Optional offset in file array.
     */
    public function __construct($file, $offset = null)
    {
        if (isset($offset)) {
            $this->tmpName = $file['tmp_name'][$offset];
            $this->name = $file['name'][$offset];
            $this->type = $file['type'][$offset];
            $this->size = $file['size'][$offset];
            $this->error = $file['error'][$offset];
        } else {
            $this->tmpName = $file['tmp_name'];
            $this->name = $file['name'];
            $this->type = $file['type'];
            $this->size = $file['size'];
            $this->error = $file['error'];
        }
    }
  
    /**
     * Get value of property.
     *
     * @param string $property Property.
     * @return mixed Value.
     * @throws InvalidPropertyException If property is undefined.
     */
    public function __get($property)
    {
        switch ($property) {
            case 'name':
            case 'type':
            case 'size':
            case 'error':
                return $this->$property;
        }
        throw new InvalidPropertyException('Undefined property: ' . $property);
    }
  
    /**
     * Whether a property is set.
     *
     * @param string $property Property.
     * @return bool True if set.
     * @throws InvalidPropertyException If property is undefined.
     */
    public function __isset($property)
    {
        switch ($property) {
            case 'name':
            case 'type':
            case 'size':
            case 'error':
                return isset($this->$property);
        }
        throw new InvalidPropertyException('Undefined property: ' . $property);
    }
  
    /**
     * {@inheritdoc}
     */
    public function moveTo($path)
    {
        if (! isset($this->tmpName)) {
            throw new UploadException('File already moved');
        }
        if (! is_uploaded_file($this->tmpName)) {
            throw new UploadException('Not an uploaded file');
        }
        if (PHP_SAPI == 'cli' or PHP_SAPI == '') {
            $dest = fopen($path, 'wb+');
            if (! $dest) {
                throw new UploadException('Could not move file');
            }
            $stream = $this->getStream();
            $stream->rewind();
            while (! $stream->eof()) {
                fwrite($dest, $stream->read(8192));
            }
            $stream->close();
            fclose($dest);
        } elseif (! move_uploaded_file($this->tmpName, $path)) {
            throw new UploadException('Could not move file');
        }
        $this->tmpName = null;
    }
  
    /**
     * Convert PHP's superglobal `$_FILES` array.
     *
     * @param array $files File array.
     * @return array An array of {@see UploadedFile} instances.
     */
    public static function convert($files)
    {
        $result = array();
        foreach ($files as $key => $file) {
            if (isset($file['tmp_name'])) {
                if (is_string($file['tmp_name'])) {
                    $result[$key] = new UploadedFile($file);
                    continue;
                }
                if (is_array($file['tmp_name'])) {
                    if (isset($file['tmp_name'][0]) and is_string($file['tmp_name'][0])) {
                        $result[$key] = array();
                        foreach ($file['tmp_name'] as $offset => $tmpName) {
                            $result[$key][] = new UploadedFile($file, $offset);
                        }
                        continue;
                    }
                }
            }
            $result[$key] = self::convert($file);
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getStream()
    {
        if (! isset($this->tmpName)) {
            throw new UploadException('File already moved');
        }
        if (! isset($this->stream)) {
            $this->stream = new PhpStream($this->tmpName, 'rb');
        }
        return $this->stream;
    }
}
