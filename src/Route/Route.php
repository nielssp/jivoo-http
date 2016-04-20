<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

use Jivoo\Http\ActionRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * A route.
 */
interface Route
{
    
    /**
     * Convert to a route string, e.g.
     * 'prefix:ClassName(parameter1, parameter2)?query#fragment'.
     *
     * @return string Route string.
     */
    public function __toString();
    
    /**
     * Get the query parameters.
     *
     * @return string[]
     */
    public function getQuery();
    
    /**
     * Get the fragment.
     *
     * @return string
     */
    public function getFragment();
    
    /**
     * Get path for route.
     *
     * @param string[]|null $pattern Pattern or null if no pattern has been
     * created for this route.
     * @return string|string[] A path as a string (absolute) or as a path array.
     */
    public function getPath($pattern);
    
    /**
     * Get the route parameters.
     *
     * @return string[]
     */
    public function getParameters();
    
    /**
     * Set query parameters.
     *
     * @param string[] $query Query parameters.
     * @return static
     */
    public function withQuery(array $query);
    
    /**
     * Set fragment.
     *
     * @param string $fragment Fragment.
     * @return static
     */
    public function withFragment($fragment);
    
    /**
     * Set route parameters.
     *
     * @param string[] $parameters Route parameters.
     * @return static
     */
    public function withParameters(array $parameters);
    
    /**
     * Strip parameters, query, and fragment.
     *
     * @return static
     */
    public function withoutAttributes();
    
    /**
     * Dispatch route, i.e. create a response.
     *
     * @param ActionRequest $request Request.
     * @param ResponseInterface $response Input response
     * @return ResponseInterface Output response.
     */
    public function dispatch(ActionRequest $request, ResponseInterface $response);
    
    /**
     * Perform auto routing.
     *
     * @param \Jivoo\Http\Route\Matcher $matcher Route matcher.
     * @param bool $resource Whether to route as a resource.
     * @return string|null Pattern to use for nested routing or null if nesting
     * is not possible.
     * @throws RouteException If auto routing is not possible.
     */
    public function auto(Matcher $matcher, $resource = false);
}
