<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Implements {@see Route}
 */
abstract class RouteBase implements Route
{
    
    /**
     * Query parameters.
     *
     * @var string[]
     */
    protected $query = [];
    
    /**
     * Path parameters.
     *
     * @var string[]
     */
    protected $parameters = [];
    
    /**
     * Fragment.
     *
     * @var string
     */
    protected $fragment = '';
    
    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withQuery(array $query)
    {
        $route = clone $this;
        $route->query = $query;
        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withParameters(array $parameters)
    {
        $route = clone $this;
        $route->parameters = $parameters;
        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->fragment;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        $route = clone $this;
        $route->fragment = $fragment;
        return $route;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withoutAttributes()
    {
        $route = clone $this;
        $route->fragment = '';
        $route->query = [];
        $route->parameters = [];
        return $route;
    }
    
    /**
     * Strip fragment, query and optionally parameters from a route string.
     *
     * @param string $routeString The route string.
     * @param array $route A route array to insert attributes into.
     * @param bool $withParameters Whether to also read parameters from the
     * route string.
     * @return string The route string without attributes.
     */
    public static function stripAttributes($routeString, &$route, $withParameters = true)
    {
        $regex = '/^(.*?)(?:\?([^?#]*))?(?:#([^#?]*))?$/';
        preg_match($regex, $routeString, $matches);
        if (isset($matches[2])) {
            parse_str($matches[2], $query);
            $route['query'] = $query;
        }
        if (isset($matches[3])) {
            $route['fragment'] = $matches[3];
        }
        if ($withParameters) {
            // TODO: implement
        }
        return $matches[1];
    }
    
    /**
     * Will replace **, :*, * and :foo in path with parameters.
     *
     * @param string[] $pattern Path pattern.
     * @param array $parameters Array of parameters.
     * @return string[] Resulting path.
     */
    public static function insertParameters(array $pattern, array $parameters)
    {
        $result = array();
        foreach ($pattern as $part) {
            if ($part == '**' or $part == ':*') {
                while (current($parameters) !== false) {
                    $result[] = array_shift($parameters);
                }
                break;
            } elseif ($part == '*') {
                $part = array_shift($parameters);
            } elseif ($part[0] == ':') {
                $var = substr($part, 1);
                $part = $parameters[$var];
                unset($parameters[$var]);
            }
            $result[] = $part;
        }
        return $result;
    }
}
