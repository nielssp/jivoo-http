<?php
// Jivoo HTTP
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * A type of route.
 */
interface Scheme
{
    /**
     * Get route string prefixes understood by this route scheme.
     *
     * @return string[] List of prefixes.
     */
    public function getPrefixes();
    
    /**
     * Get route array keys understood by this route scheme.
     *
     * @return string[] List of keys.
     */
    public function getKeys();
     
    /**
     * Convert a route array to a route object. The array is guaranteed to
     * have a one of the keys returned by {@see getKeys}.
     *
     * @param array $route Route array.
     * @return Route A route.
     * @throw RouteException If format is invalid.
     */
    public function fromArray(array $route);
  
    /**
     * Convert a route string to a route object. The string is guaranteed to
     * have a one of the prefixes returned by {@see getPrefixes}.
     *
     * @param string $routeString A route string, e.g.
     * 'prefix:ClassName(parameter1, parameter2)?query#fragment'.
     * @return Route A route.
     * @throws RouteException If format of `$routeString` is invalid.
     */
    public function fromString($routeString);
}
