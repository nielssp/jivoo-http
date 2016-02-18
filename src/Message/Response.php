<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * An HTTP response.
 */
class Response extends Message implements \Psr\Http\Message\ResponseInterface
{
    
    /**
     * @var int
     */
    private $status = 200;
    
    /**
     * @var string
     */
    private $reason = 'OK';
    
    /**
     * Construct a response object.
     *
     * @param int $status Status code.
     * @param string|resource|\Psr\Http\Message\StreamInterface $body Response
     * body.
     */
    public function __construct($status, $body = 'php://memory')
    {
        if (! ($body instanceof \Psr\Http\Message\StreamInterface)) {
            $body = new PhpStream($body, 'wb+');
        }
        parent::__construct($body);
        $this->status = $status;
        $this->reason = Status::phrase($status);
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        return $this->reason;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $response = clone $this;
        $response->status = $code;
        if ($reasonPhrase == '') {
            $reasonPhrase = Status::phrase($code);
        }
        $response->reason = $reasonPhrase;
        return $response;
    }
    
    /**
     * Create a redirect response.
     *
     * @param string $location Redirect target.
     * @param bool $permanent Whether redirect is permanent.
     * @return \self The response.
     */
    public static function redirect($location, $permanent = false)
    {
        $response = new self(
            $permanent ? Status::MOVED_PERMANENTLY : Status::SEE_OTHER,
            new StringStream('')
        );
        $response->setHeader('Location', $location);
        return $response;
    }
    
    /**
     * Create a file response.
     *
     * @param string $path Path to file.
     * @param string|null $type Optional MIME type.
     * @return \self The response.
     */
    public static function file($path, $type = null)
    {
        $response = new self(
            Status::OK,
            new PhpStream($path, 'rb')
        );
        if (isset($type)) {
            $response->setHeader('Content-Type', $type);
        }
        return $response;
    }
}
