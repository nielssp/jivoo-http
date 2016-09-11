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
     * @var Cookie\CookiePool|null
     */
    private $cookies;
    
    /**
     * @var callable
     */
    private $handler;
    
    /**
     * @var callable[]
     */
    private $middleware = [];
    
    /**
     * @var string|null
     */
    private $compression = null;
    
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
     * @return Cookie\CookiePool
     */
    public function getCookies()
    {
        if (! isset($this->cookies)) {
            $this->cookies = new Cookie\CookiePool();
            foreach ($this->request->getCookieParams() as $name => $value) {
                $this->cookies->add(new Cookie\MutableCookie($name, $value));
            }
        }
        return $this->cookies;
    }
    
    /**
     * Set compression algorithm.
     *
     * @param string|null $compression Compression algorithm: 'bzip2', 'gzip' or
     * null.
     */
    public function setCompression($compression)
    {
        $this->compression = $compression;
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
     * @param Cookie\ResponseCookie[]|\Traversable $cookies
     * @throws HeadersSentException If the headers have already been sent.
     */
    public function serve(ResponseInterface $response, $cookies = [])
    {
        if (headers_sent($file, $line)) {
            throw new HeadersSentException(
                'Headers already sent in ' . $file . ' on line ' . $line
            );
        }
        $this->serveStatus($response);
        $this->serveCookies($cookies);
        $this->serveHeaders($response);
        $this->serveBody($response, $this->compression);
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
     * Set the cookies.
     *
     * @param Cookie\ResponseCookie[]|\Traversable $cookies
     */
    protected function serveCookies($cookies)
    {
        foreach ($cookies as $cookie) {
            if ($cookie->hasChanged()) {
                $expiration = $cookie->getExpiration();
                if (isset($expiration)) {
                    $expiration = $expiration->getTimestamp();
                } else {
                    $expiration = 0;
                }
                setcookie(
                    $cookie->getName(),
                    $cookie->get(),
                    $expiration,
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->isSecure(),
                    $cookie->isHttpOnly()
                );
            }
        }
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
     * @param string $compression Compression to use: 'bzip2' or 'gzip'.
     */
    protected function serveBody(ResponseInterface $response, $compression = null)
    {
        $body = $response->getBody();
        $body->rewind();
        $out = fopen('php://output', 'wb');
        if ($compression == 'bzip2') {
            fwrite($out, bzcompress($body->getContents()));
        } elseif ($compression == 'gzip') {
            fwrite($out, gzencode($body->getContents()));
        } else {
            while (! $body->eof()) {
                fwrite($out, $body->read(8192));
            }
        }
        fclose($out);
        $body->close();
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
        $this->serve($response, $this->getCookies());
    }
}
