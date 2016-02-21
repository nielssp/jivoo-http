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
     *
     * @param string|array $patternOrPatterns
     * @param string|array|Route|HasRoute|null $route
     * @param int $priority
     * @return Matcher|null
     */
    public function match($patternOrPatterns, $route = null, $priority = 5);
    
    /**
     *
     * @param string|array|Route|HasRoute A route.
     * @return Matcher
     */
    public function resource($route);
}
