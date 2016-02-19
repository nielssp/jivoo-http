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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A simple server object with support for middleware.
 */
class SapiServer extends EventSubjectBase
{
 
    /**
     * @var ServerRequestInterface
     */
    private $request;
    
    /**
     * @var callable
     */
    private $handler;
    
    /**
     * @var callable[]
     */
    private $middleware = [];
    
    /**
     * Construct SAPI server.
     *
     * @param callable|null $handler Optional request handler. Must accept two
     * parameters, a {@see ServerRequestInterface} object and a
     * {@see ResponseInterface} object, and return a {@see ResponseInterface}
     * object.
     * @param ServerRequestInterface|null $request Optional request to handle.
     * The default value is created from PHP's superglobals, see
     * {@see Request::createGlobal}.
     */
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

    /**
     * Add middleware.
     *
     * @param Middleware|callable $middleware Middleware function, should have
     * the same signature as {@see Middleware::__invoke}, but does not need to
     * be an object of the {@see Middleware} interface.
     */
    public function add(callable $middleware)
    {
        array_unshift($this->middleware, $middleware);
    }
    
    /**
     * Serve a response.
     *
     * @param ResponseInterface $response The response.
     * @throws HeadersSentException If the headers have already been sent.
     */
    protected function serve(ResponseInterface $response)
    {
        if (headers_sent($file, $line)) {
            throw new HeadersSentException(
                'Headers already sent in ' . $file . ' on line ' . $line
            );
        }
        $this->serveStatus($response);
        $this->serveHeaders($response);
        $this->serveBody($response);
    }
    
    /**
     * Set the status line.
     *
     * @param ResponseInterface $response The response.
     */
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
    
    /**
     * Set the headers.
     *
     * @param ResponseInterface $response The response.
     */
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
    
    /**
     * Output the response body.
     *
     * @param ResponseInterface $response The response.
     */
    protected function serveBody(ResponseInterface $response)
    {
        $out = fopen('php://output', 'wb');
        $body = $response->getBody();
        $body->rewind();
        while (! $body->eof()) {
            fwrite($out, $body->read(8192));
        }
        $body->close();
        fclose($out);
    }
    
    /**
     * @param callable[] $middleware
     * @return callable
     */
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
    
    /**
     * Start server, i.e. create a response for the current request using the
     * available middleware and request handler.
     */
    public function listen()
    {
        $request = $this->request;
        $response = new Response(Status::OK);
        $first = $this->getNext($this->middleware);
        $response = $first($request, $response);
        $this->serve($response);
    }
}
