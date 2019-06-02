<?php
// Jivoo HTTP
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

use Psr\Http\Message\ServerRequestInterface;

/**
 * A request.
 */
class Request extends Message implements ServerRequestInterface
{

    use RequestTrait, ServerRequestTrait;
    
    /**
     * Construct request.
     *
     * @param Uri $uri The URI.
     * @param string $method Request method, e.g. 'GET', 'POST', 'PUT', 'PATCH',
     * or 'DELETE'.
     * @param array $query Query.
     * @param array $data POST data.
     * @param array $cookies Cookie parameters.
     * @param array $files Uploaded files as instances of {@see UploadedFile}.
     * @param array $server Server parameters.
     */
    public function __construct(
        Uri $uri,
        $method = 'GET',
        array $query = [],
        array $data = [],
        array $cookies = [],
        array $files = [],
        array $server = []
    ) {
        parent::__construct(new PhpStream('php://input', 'r'));
        $this->uri = $uri;
        $host = $uri->getHost();
        if ($host != '') {
            $this->setHeader('Host', $host);
        }
        $this->method = $method;
        $this->query = $query;
        $this->data = $data;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;
    }
    
    public function withServerParams(array $server)
    {
        $request = clone $this;
        $request->server = $server;
        return $request;
    }

    /**
     * Create a request from PHP's superglobals.
     *
     * @return self The request.
     */
    public static function createGlobal()
    {
        $uri = new Uri(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $request = new self(
            $uri,
            $method,
            $_GET,
            $_POST,
            $_COOKIE,
            UploadedFile::convert($_FILES),
            $_SERVER
        );
        foreach ($_SERVER as $name => $value) {
            if (substr_compare($name, 'HTTP_', 0, 5) === 0) {
                $request->setHeader(str_replace('_', '-', substr($name, 5)), $value);
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $request->setHeader('Content-Type', $_SERVER['CONTENT_TYPE']);
        }
        return $request;
    }
    
    /**
     * Create a request.
     *
     * @param string $uri URI as a string.
     * @param string $method Request method.
     * @param array $queryOrData Query (GET) or data (POST/PUT/DELETE/PATCH).
     * @return \self The request.
     */
    public static function create($uri, $method = 'GET', array $queryOrData = [])
    {
        $method = strtoupper($method);
        $query = [];
        $data = [];
        switch ($method) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                $data = $queryOrData;
                break;
            default:
                $query = $queryOrData;
                break;
        }
        return new self(new Uri($uri), $method, $query, $data);
    }
}
