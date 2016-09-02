<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Enables compression if available.
 */
class Compressor implements Middleware
{
    
    /**
     * @var SapiServer
     */
    private $server;
    
    /**
     * Construct compressor.
     *
     * @param SapiServer $server SAPI server.
     */
    public function __construct(SapiServer $server)
    {
        $this->server = $server;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if (!($request instanceof ActionRequest)) {
            $request = new ActionRequest($request);
        }
        $response = $next($request, $response);
        if (function_exists('bzcompress') and $request->acceptsEncoding('bzip2')) {
            $response = $response->withHeader('Content-Encoding', 'bzip2');
            $this->server->setCompression('bzip2');
        } elseif (function_exists('gzencode') and $request->acceptsEncoding('gzip')) {
            $response = $response->withHeader('Content-Encoding', 'gzip');
            $this->server->setCompression('gzip');
        }
        return $response;
    }
}
