<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action router.
 */
class Router implements Middleware, Route\Matcher
{
    
    public function __construct(\Jivoo\Store\Document $config = null)
    {
    }
    
    /**
     * 
     * @param string|array|Route|HasRoute $route
     * @return Route Validated route.
     * @throws RouteError
     */
    public function validate($route) {
        return $route;
    }
    
    /**
     * {@inheritdoc}
     */
    public function match($patternOrPatterns, $route, $priority = 5)
    {
        if (is_array($patternOrPatterns)) {
            foreach ($patternOrPatterns as $pattern => $route) {
                $this->match($pattern, $route);
            }
            return;
        }
        throw \Exception('not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function resource($route)
    {
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $request = new ActionRequest($request);
        
        // find path
        // find route
        // apply router middleware
        // call action
        
        return $response;
    }

}
