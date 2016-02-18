<?php
// Jivoo HTTP
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

use Jivoo\EventSubjectBase;
use Jivoo\Http\Message\Request;
use Jivoo\Http\Message\Response;
use Jivoo\Http\Message\Status;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of SapiServer
 */
class SapiServer extends EventSubjectBase
{
    
    private $request;
    
    private $handler;
    
    private $middleware = [];
    
    public function __construct($handler = null, ServerRequestInterface $request = null)
    {
        parent::__construct();
        if (! isset($handler)) {
            $handler = function (ServerRequestInterface $request, ResponseInterface $response) {
                return $response;
            };
        }
        $this->handler = $handler;
        if (! isset($request)) {
            $request = Request::createGlobal();
        }
        $this->request = $request;
    }
    
    public function close()
    {
    }

    public function add(callable $middleware)
    {
        $this->middleware[] = $middleware;
    }
    
    protected function serve(ResponseInterface $response)
    {
        $this->serveStatus($response);
        $this->serveHeaders($response);
        $this->serveBody($response);
    }
    
    protected function serveStatus(ResponseInterface $response)
    {
        $protocol = $response->getProtocolVersion();
        $status = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        if ($reason != '') {
            $reason = ' ' . $reason;
        }
        header('HTTP/' . $protocol . ' ' . $status . $reason);
    }
    
    protected function serveHeaders(ResponseInterface $response)
    {
        header_remove('X-Powered-By');
        foreach ($response->getHeaders() as $header => $values) {
            header($header . ': ' . array_shift($values), true);
            foreach ($values as $value) {
                header($header . ': ' . $value, false);
            }
        }
    }
    
    protected function serveBody(ResponseInterface $response)
    {
        echo $response->getBody();
    }
    
    private function getNext(array $middleware)
    {
        if (count($middleware)) {
            return function (ServerRequestInterface $request, ResponseInterface $response) use ($middleware) {
                $next = array_shift($middleware);
                return $next($request, $response, $this->getNext($middleware));
            };
        } else {
            return $this->handler;
        }
    }
    
    public function listen()
    {
        $request = $this->request;
        $response = new Response(Status::OK);
        $first = $this->getNext($this->middleware);
        $response = $first($request, $response);
        $this->serve($response);
    }
}
