<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Implements entity tag cache mechanism. A response handled by this middleware
 * should include an 'ETag'-header.
 */
class EntityTag implements Middleware
{

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response = $next($request, $response);
        if ($response->hasHeader('ETag')) {
            $etag = $response->getHeaderLine('ETag');
            if ($request->hasHeader('If-None-Match')) {
                $tags = $request->getHeader('If-None-Match');
                foreach ($tags as $tag) {
                    if ($tag === $etag) {
                        $response = $response->withStatus(Message\Status::NOT_MODIFIED);
                        $response = $response->withBody(new Message\StringStream(''));
                    }
                }
            }
            $response = $response
                ->withHeader('Cache-Control', 'must-revalidate')
                ->withHeader('Pragma', 'must-revalidate');
        }
        return $response;
    }

}
