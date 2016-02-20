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
    
    private $paths = [];
    
    private $patterns;
    
    private $schemes = [];
    
    public function __construct(\Jivoo\Store\Document $config = null)
    {
        $this->patterns = new \SplPriorityQueue();
    }
    
    public function addScheme(Route\Scheme $scheme)
    {
        $prefixes = $scheme->getPrefixes();
        foreach ($prefixes as $prefix) {
            $this->schemes[$prefix] = $scheme;
        }
    }
    
    /**
     *
     * @param string|array|Route|HasRoute $route
     * @return Route\Route Validated route.
     * @throws RouteError
     */
    public function validate($route)
    {
        if ($route instanceof Route\Route) {
            return $route;
        }
        if ($route instanceof Route\HasRoute) {
            return $this->validate($route->getRoute());
        }
        throw new \Exception('Not implemented');
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
        $pattern = $patternOrPatterns;
        $route = $this->validate($route);
        
        $pattern = explode(' ', $pattern, 2);
        $method = 'ANY';
        if (count($pattern) > 1) {
            $method = strtoupper($pattern[0]);
            $pattern = $pattern[1];
        } else {
            $pattern = $pattern[0];
        }
        if (! isset($this->index[$method])) {
            $this->index[$method] = [];
        }
        
        $this->patterns->insert([
            'method' => $method,
            'pattern' => explode('/', trim($pattern, '/')),
            'route' => $route,
            'priority' => $priority
        ], $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function resource($route)
    {
        $route = $this->validate($route);
        $route->auto($this, true);
    }
    
    public function addPath($route, array $pattern, $arity, $priority = 5)
    {
        $route = $this->validate($route);
        $key = $route->__toString() . '[' . $arity . ']';
        if (isset($this->paths[$key])) {
            if ($priority < $this->paths[$key]['priority']) {
                return false;
            }
        }
        $this->paths[$key] = [
            'pattern' => $pattern,
            'priority' => $priority
        ];
    }
    
    public function applyPattern(array $pattern, array $path)
    {
        $length = count($pattern);
        if ($length < count($path) and $pattern[$length - 1] != '**'
            and $pattern[$length - 1] != ':*') {
            return null;
        }
        $parameters = [];
        foreach ($pattern as $j => $part) {
            if ($part == '**' || $part == ':*') {
                $parameters = array_merge(
                    $parameters,
                    array_slice($path, $j)
                );
                break;
            }
            if ($path[$j] == $part) {
                continue;
            }
            if ($part == '*') {
                $parameters[] = $path[$j];
                continue;
            }
            if (isset($part[0]) and $part[0] == ':') {
                $var = substr($part, 1);
                if (is_numeric($var)) {
                    $parameters[(int)$var] = $path[$j];
                } else {
                    $parameters[$var] = $path[$j];
                }
                continue;
            }
            return null;
        }
        return $parameters;
    }
    
    public function findMatch(array $path, $method)
    {
        foreach ($this->patterns as $pattern) {
            if ($pattern['method'] != 'ANY' and $pattern['method'] != $method) {
                continue;
            }
            $parameters = $this->applyPattern($pattern['pattern'], $path);
            if (isset($parameters)) {
                return $pattern['route']->withParameters($parameters);
            }
        }
        return null;
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
