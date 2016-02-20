<?php
// Jivoo HTTP
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Implements a type of route.
 */
interface Dispatcher
{
    /**
     * Get route string prefixes understood by this dispatcher.
     *
     * @return string[] List of prefixes.
     */
    public function getPrefixes();
    
    /**
     * Get route array keys understood by this dispatcher.
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
     * @param string $routeString Route string, e.g. 'prefix:ClassName'.
     * @return Route A route.
     * @throw RouteException If format is invalid.
     */
    public function fromString($routeString);
}
