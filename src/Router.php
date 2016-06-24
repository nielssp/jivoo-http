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
 *
 * An action is a function from ({@see ActionRequest}, {@see ResponseInterface})
 * to {@see ResponseInterface}.
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
    
    /**
     * Construct router.
     *
     * @param \Jivoo\Store\Document $config Optional router configuration
     * document.
     */
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
     * be an object of the {@see Middleware} interface. The request-object
     * passed to the function is always an instance of {@see ActionRequest}.
     */
    public function add(callable $middleware)
    {
        array_unshift($this->middleware, $middleware);
    }
    
    /**
     * Enable HTTP rewrite.
     *
     * @param bool $enable Enable.
     */
    public function enableRewrite($enable = true)
    {
        $this->rewrite = $enable;
    }
    
    /**
     * Disable HTTP rewrite.
     */
    public function disableRewrite()
    {
        $this->rewrite = false;
    }

    /**
     * Validate a route.
     *
     * @param string|array|Route|HasRoute $route A route array, string, or
     * object.
     * @return Route\Route Validated route.
     * @throws Route\RouteError If the route is invalid.
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
                $route = $this->findMatch([], 'GET');
                if (! isset($route)) {
                    throw new Route\RouteException('No root route');
                }
                return $route;
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
    public function root($route)
    {
        $this->match('', $route, 10);
    }
    
    /**
     * {@inheritdoc}
     */
    public function error($route)
    {
        $this->match('**', $route, 0);
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
            return null;
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
        
        if ($pattern == ['']) {
            $pattern = [];
        }
        
        $arity = 0;
        foreach ($pattern as $part) {
            if ($part == '**' or $part == ':*') {
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
        
        return new Route\NestedMatcher($this, [$this, 'validate'], $patternOrPatterns);
    }

    /**
     * {@inheritdoc}
     */
    public function auto($route)
    {
        $route = $this->validate($route);
        $pattern = $route->auto($this, false);
        if (! isset($pattern)) {
            return null;
        }
        return new Route\NestedMatcher($this, $this->validator, $pattern);
    }

    /**
     * {@inheritdoc}
     */
    public function resource($route)
    {
        $route = $this->validate($route);
        $pattern = $route->auto($this, true);
        if (! isset($pattern)) {
            return null;
        }
        return new Route\NestedMatcher($this, $this->validator, $pattern);
    }
    
    /**
     * Add a path for a route.
     *
     * @param string|array|Route|HasRoute $route A route.
     * @param string[] $pattern Path pattern.
     * @param int|string $arity Pattern arity, i.e. number of variables. '*'
     * for variadic.
     * @param int $priority Path priority.
     */
    public function addPath($route, array $pattern, $arity, $priority = 5)
    {
        $route = $this->validate($route);
        $key = $route->__toString() . '[' . $arity . ']';
        if (isset($this->paths[$key])) {
            if ($priority < $this->paths[$key]['priority']) {
                return;
            }
        }
        $this->paths[$key] = [
            'pattern' => $pattern,
            'priority' => $priority
        ];
    }
    
    /**
     * Get path for a validated route.
     *
     * @param \Jivoo\Http\Route\Route $route Validated route.
     * @return string[]|string Path array or absolute path string.
     * @throws Route\RouteException If the a path could not be found for
     * the route.
     */
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
    
    /**
     * Get path for a route.
     *
     * @param string|array|Route|HasRoute $route A route.
     * @return string[]|string Path array or absolute path string.
     */
    public function getPath($route)
    {
        $route = $this->validate($route);
        return $this->getPathValidated($route);
    }
    
    /**
     * Get URI for a route.
     *
     * @param string|array|Route|HasRoute $route A route.
     * @return Message\Uri Uri.
     */
    public function getUri($route, $full = false)
    {
        $route = $this->validate($route);
        $path = $this->getPathValidated($route);
        if (is_string($path)) {
            return new Message\Uri($path);
        }
        if ($full) {
            $uri = $this->request->getUri()->withPath($this->request->pathToString($path));
        } else {
            $uri = new Message\Uri($this->request->pathToString($path));
        }
        $uri = $uri->withQuery(http_build_query($route->getQuery()))
            ->withFragment($route->getFragment());
        return $uri;
    }
    
    /**
     * Apply a path pattern to a path.
     *
     * @param string[] $pattern Path pattern.
     * @param string[] $path Path.
     * @return string[]|null An array of path parameters or null if not a match.
     */
    public function applyPattern(array $pattern, array $path)
    {
        $length = count($pattern);
        if ($length < count($path) and $pattern[$length - 1] != '**'
            and $pattern[$length - 1] != ':*') {
            return null;
        }
        $parameters = [];
        foreach ($pattern as $j => $part) {
            if ($part == '**' or $part == ':*') {
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
    
    /**
     * Find a route for a path.
     *
     * @param string[] $path Path array.
     * @param string $method Request method.
     * @return Route|null A route or null if none found.
     */
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
     * @return ResponseInterface A redirect response.
     */
    public function redirectPath($path, array $query = [], $fragment = '', $permanent = false, $rewrite = false)
    {
        $location = new Message\Uri($this->request->pathToString($path, $rewrite));
        $location = $location->withQuery(http_build_query($query))
            ->withFragment($fragment);
        return Message\Response::redirect($location, $permanent);
    }
    
    /**
     * Create a route redirect.
     *
     * @param string|array|Route|HasRoute $route A route.
     * @param bool $permanent Whether redirect is permanent.
     * @return ResponseInterface A redirect response.
     */
    public function redirect($route, $permanent = false)
    {
        $route = $this->validate($route);
        return $this->redirectPath($this->getPath($route), $route->getQuery(), $route->getFragment(), $permanent);
    }
    
    /**
     * Create a refresh response.
     *
     * @param array $query Optional new query parameters.
     * @param string $fragment Optional fragment.
     * @return ResponseInterface A refresh response.
     */
    public function refresh($query = null, $fragment = '')
    {
        if (! isset($query)) {
            $query = $this->request->query;
        }
        return $this->redirectPath($this->request->path, $query, $fragment);
    }
    
    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $this->request = new ActionRequest($request);
        
        $this->request = $this->request->withAttribute('rewrite', $this->rewrite);
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
}
