<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * A path matcher used in routing.
 */
interface Matcher
{

    /**
     * Set the root route. Same as `match('', $route, 10)`.
     *
     * @param string|array|Route|HasRoute $route A route, see
     * {@see \Jivoo\Http\Router}.
     */
    public function root($route);
    
    /**
     * Set the error route. Same as `match('**', $route, 0)`.
     *
     * @param string|array|Route|HasRoute $route A route, see
     * {@see \Jivoo\Http\Router}.
     */
    public function error($route);
       
    /**
     * Match one or more patterns.
     *
     * @param string|array $patternOrPatterns A single pattern or an associative
     * array mapping patterns to routes.
     * @param string|array|Route|HasRoute|null $route A route, see
     * {@see \Jivoo\Http\Router}.
     * @param int $priority Priority of pattern.
     * @return Matcher|null A nested matcher (if single pattern) or null (if
     * multiple patterns).
     */
    public function match($patternOrPatterns, $route = null, $priority = 5);
    
    /**
     *
     * @param string|array|Route|HasRoute $route
     * @return Matcher|null
     */
    public function auto($route);
    
    /**
     *
     * @param string|array|Route|HasRoute A route.
     * @return Matcher|null
     */
    public function resource($route);
}
