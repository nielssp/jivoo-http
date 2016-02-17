<?php
// Jivoo HTTP
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * A request.
 */
class Request extends Message implements \Psr\Http\Message\ServerRequestInterface
{

    use RequestTrait, ServerRequestTrait;
    
    public function __construct(
        Uri $uri,
        $method = 'GET',
        $query = [],
        $data = [],
        $cookies = [],
        $files = [],
        $server = []
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
        return $request;
    }
    
    public static function create($uri, $method = 'GET', $queryOrData = [])
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
