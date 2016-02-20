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
     * Convert to a route string, e.g. 'prefix:ClassName'.
     * 
     * @return string Route string.
     */
    public function __toString();
    
    /**
     * @return string
     */
    public function getKey();
    
    /**
     * @return string[]
     */
    public function getQuery();
    
    /**
     * @return string
     */
    public function getFragment();
    
    /**
     * 
     * @param string[] $pattern
     * @return string|string[]
     */
    public function getPath($pattern);
    
    /**
     * return array
     */
    public function getParameters();
    
    /**
     * 
     * @param ActionRequest $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function dispatch(ActionRequest $request, ResponseInterface $response);
    
    /**
     * 
     * @param \Jivoo\Http\Route\Matcher $matcher
     * @param bool $resource
     */
    public function auto(Matcher $matcher, $resource = false);
}
