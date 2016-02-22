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
 
    /**
     * @var ActionRequest|null
     */
    private $request = null;
    
    /**
     * @var Route\Route|null
     */
    private $route = null;
    
    /**
     * @var array
     */
    private $paths = [];
    
    /**
     * @var array
     */
    private $patterns;
    
    /**
     * @var Route\Scheme[]
     */
    private $schemePrefixes = [];
    
    /**
     * @var Route\Scheme[]
     */
    private $schemeKeys = [];
    
    public function __construct(\Jivoo\Store\Document $config = null)
    {
        $this->patterns = new \SplPriorityQueue();
    }
    
    public function addScheme(Route\Scheme $scheme)
    {
        $prefixes = $scheme->getPrefixes();
        foreach ($scheme->getPrefixes() as $prefix) {
            $this->schemePrefixes[$prefix] = $scheme;
        }
        foreach ($scheme->getKeys() as $key) {
            $this->schemeKeys[$key] = $scheme;
        }
    }
    
    /**
     *
     * @param string|array|Route|HasRoute $route
     * @return Route\Route Validated route.
     * @throws Route\RouteError
     * @throws \Jivoo\InvalidArgumentException If `$route` is not a recognized
     * type.
     */
    public function validate($route)
    {
        if ($route instanceof Route\Route) {
            return $route;
        }
        if ($route instanceof Route\HasRoute) {
            return $this->validate($route->getRoute());
        }
        if (is_string($route)) {
            if ($route == '') {
                if (isset($this->root)) {
                    return $this->root;
                }
                $route = ['path' => []];
            } else {
                if (preg_match('/^([a-zA-Z0-9\.\-+]+):/', $route, $matches) === 1) {
                    $prefix = $matches[1];
                    if (isset($this->schemePrefixes[$prefix])) {
                        $scheme = $this->schemePrefixes[$prefix];
                        return $scheme->fromString($route);
                    }
                    throw new Route\RouteException('Unknown route scheme: ' . $prefix);
                }
                // TODO: use current scheme .. e.g. 'action:' if in a controller
                throw new Route\RouteException('Missing route scheme');
            }
        }
        \Jivoo\Assume::isArray($route);
                
        $default = [
            'parameters' => null,
            'query' => null,
            'fragment' => null,
            'mergeQuery' => false
        ];
        $scheme = null;
        $parameters = [];
        foreach ($route as $key => $value) {
            if (is_int($key)) {
                $parameters[] = $value;
            } elseif ($key == 'paremeters') {
                $parameters = array_merge($parameters, $value);
            } elseif (in_array($key, ['query', 'fragment', 'mergeQuery'])) {
                $default[$key] = $value;
            } elseif (isset($this->schemeKeys[$key])) {
                $default[$key] = $value;
                if (! isset($scheme)) {
                    $scheme = $this->schemeKeys[$key];
                }
            } else {
                throw new Route\RouteException('Undefined key in route: ' . $key);
            }
        }
        $route = $default;
        if (count($parameters)) {
            $route['parameters'] = $parameters;
        }
        if ($route['mergeQuery']) {
            $query = [];
            if (isset($this->request)) {
                $query = $this->request->getQueryParams();
            }
            if (isset($route['query'])) {
                $query = merge_array($query, $route['query']);
            }
            $route['query'] = $query;
        }
        unset($route['mergeQuery']);
        
        if (isset($scheme)) {
            return $scheme->fromArray($route);
        }
        if (! isset($this->route)) {
            throw new Route\RouteException('Unknown route scheme');
        }
        $copy = $this->route;
        if (isset($route['parameters'])) {
            $copy = $copy->withParameters($route['parameters']);
        }
        if (isset($route['query'])) {
            $copy = $copy->withQuery($route['query']);
        }
        if (isset($route['fragment'])) {
            $copy = $copy->withFragment($route['fragment']);
        }
        return $copy;
    }
    
    /**
     * {@inheritdoc}
     */
    public function match($patternOrPatterns, $route = null, $priority = 5)
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
        $pattern = explode('/', trim($pattern, '/'));
        
        $arity = 0;
        foreach ($pattern as $part) {
            if ($part == '**' || $part == ':*') {
                $arity = '*';
                break;
            } elseif ($part == '*') {
                $arity++;
            } elseif (isset($part[0]) and $part[0] == ':') {
                $arity++;
            }
        }
        $this->addPath($route, $pattern, $arity, $priority);
        
        $this->patterns->insert([
            'method' => $method,
            'pattern' => $pattern,
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
    
    public function getPathValidated(Route\Route $route)
    {
        $parameters = $route->getParameters();
        if (count($parameters)) {
            $arity = '[' . count($parameters) . ']';
        } else {
            $arity = '[0]';
        }
        $key = $route->withoutAttributes()->__toString();
        $pattern = null;
        if (isset($this->paths[$key . $arity])) {
            $pattern = $this->paths[$key . $arity]['pattern'];
        } elseif (isset($this->paths[$key . '[*]'])) {
            $pattern = $this->paths[$key . '[*]']['pattern'];
        }
        $path = $route->getPath($pattern);
        if (! isset($path)) {
            throw new Route\RouteException('Could not find path for: ' . $key . $arity);
        }
        return $path;
    }
    
    public function getPath($route)
    {
        $route = $this->validate($route);
        return $this->getPathValidated($route);
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
        $this->request = new ActionRequest($request);
        
        // find route
        // apply router middleware
        // call action
        
        return $response;
    }
}
