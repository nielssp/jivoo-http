<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Description of NestedMatcher
 */
class NestedMatcher implements Matcher
{
    private $parent;
    
    private $prefix;
    
    public function __construct(Matcher $parent, $prefix = '')
    {
        $this->parent = $parent;
        $this->prefix = $prefix;
    }
    
    public function match($patternOrPatterns, $route = null, $priority = 5)
    {
        if (is_array($patternOrPatterns)) {
            foreach ($patternOrPatterns as $pattern => $route) {
                $this->match($pattern, $route);
            }
            return;
        }
        return $this->parent->match($this->prefix . '/' . $patternOrPatterns, $route, $priority);
    }

    public function resource($route)
    {
        
    }

    public function auto($route)
    {
        
    }
}
