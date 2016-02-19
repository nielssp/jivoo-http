<?php
// Jivoo HTTP
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for HTTP middleware.
 */
interface Middleware
{

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request.
     * @param ResponseInterface $response The response.
     * @param callable $next The next middleware function. Should be called with
     * a {@see ServerRequestInterface} object and a {@see ResponseInterface}
     * object.
     * @return ResponseInterface The output response.
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next);
}
