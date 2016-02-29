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
    private $patterns = [];
    
    /**
     * @var Route\Scheme[]
     */
    private $schemePrefixes = [];
    
    /**
     * @var Route\Scheme[]
     */
    private $schemeKeys = [];
    
    /**
     * @var callable[]
     */
    private $middleware = [];
    
    /**
     * @var bool
     */
    private $rewrite = false;
    
    public function __construct(\Jivoo\Store\Document $config = null)
    {
    }
    
    /**
     * Add a route scheme.
     *
     * @param \Jivoo\Http\Route\Scheme $scheme Scheme.
     */
    public function addScheme(Route\Scheme $scheme)
    {
        foreach ($scheme->getPrefixes() as $prefix) {
            $this->schemePrefixes[$prefix] = $scheme;
        }
        foreach ($scheme->getKeys() as $key) {
            $this->schemeKeys[$key] = $scheme;
        }
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
    
    public function enableRewrite($enable = true)
    {
        $this->rewrite = $enable;
    }
    
    public function disableRewrite()
    {
        $this->rewrite = false;
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
    
    public function root($route) {
        return $this->match('', $route, 10);
    }
    
    public function error($route) {
        return $this->match('**', $route, 0);
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
        
        $this->patterns[] = [
            'method' => $method,
            'pattern' => $pattern,
            'route' => $route,
            'priority' => $priority
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function auto($route)
    {
        $route = $this->validate($route);
        $route->auto($this, false);
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
            } elseif (! isset($path[$j])) {
                return null;
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
        usort($this->patterns, ['Jivoo\Utilities', 'prioritySorter']);
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
     * @param callable[] $middleware
     * @param callable $last
     * @return callable
     */
    private function getNext(array $middleware, callable $last)
    {
        return function (ServerRequestInterface $request, ResponseInterface $response) use ($middleware, $last) {
            if (! ($request instanceof ActionRequest)) {
                $request = new ActionRequest($request);
            }
            $this->request = $request;
            if (count($middleware)) {
                $next = array_shift($middleware);
                return $next($request, $response, $this->getNext($middleware));
            }
            return $last($request, $response);
        };
    }
    
    /**
     * Create a path redirect.
     *
     * @param string|string[] $path Path array.
     * @param array $query Query.
     * @param string $fragment Fragment.
     * @param bool $permanent Whether redirect is permanent.
     * @param bool $rewrite Whether to force removal of script name from path.
     * @return Message\Response A redirect response.
     */
    public function redirectPath($path, array $query = [], $fragment = '', $permanent = false, $rewrite = false)
    {
        $location = new Message\Uri($this->pathToString($path, $rewrite));
        $location = $location->withQuery(http_build_query($query))
            ->withFragment($fragment);
        return Message\Response::redirect($location, $permanent);
    }
    
    public function redirect($route, $permanent = false)
    {
        $route = $this->validate($route);
        return $this->redirectPath($this->getPath($route), $route->getQuery(), $route->getFragment(), $permanent);
    }
    
    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $this->request = new ActionRequest($request);
        
        $path = $this->request->path;
        if (! $this->rewrite) {
            if (!isset($path[0]) or $path[0] != $this->request->scriptName) {
                return $this->redirectPath($path, $this->request->query, '', true);
            }
            array_shift($path);
            $this->request = $this->request->withAttribute('path', $path);
        }
        if (count($path) > 0 and $path[count($path) - 1] === '') {
            return $this->redirectPath($path, $this->request->query, '', true);
        }
        
        $this->route = $this->findMatch($path, $this->request->getMethod());
        if (! isset($this->route)) {
            throw new Route\RouteException('No route found for path: ' . implode('/', $path));
        }
        $middleware = $this->middleware;
        
        $first = $this->getNext($middleware, [$this->route, 'dispatch']);
        $response = $first($this->request, $response);
        return $response;
    }
    
    /**
     * Convert path array to a string.
     *
     * @param string|string[] $path Path array or absolute url.
     * @param bool $rewrite Whether to force removal of script name from path.
     * @return string Path string.
     */
    public function pathToString($path, $rewrite = false)
    {
        if (is_string($path)) {
            return $path;
        }
        $str = $this->request->basePath;
        if ($str == '/') {
            $str = '';
        }
        if (! ($this->rewrite or $rewrite)) {
            $str .= '/' . $this->request->scriptName;
        }
        $str .= '/' . implode('/', array_map('urlencode', $path));
        $str = rtrim($str, '/');
        if ($str == '') {
            return '/';
        }
        return $str;
    }
}
