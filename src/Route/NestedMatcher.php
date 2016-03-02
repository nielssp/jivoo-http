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
    
    private $validator;
    
    private $prefix;
    
    public function __construct(Matcher $parent, callable $validator, $prefix = '')
    {
        $this->parent = $parent;
        $this->validator = $validator;
        $this->prefix = $prefix;
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
        return $this->parent->match($this->prefix . '/' . $patternOrPatterns, $route, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function resource($route)
    {
        $route = call_user_func($this->validator, $route);
        $pattern = $route->auto($this, true);
        if (! isset($pattern)) {
            return null;
        }
        return new NestedMatcher($this, $this->validator, $pattern);
    }

    /**
     * {@inheritdoc}
     */
    public function auto($route)
    {
        $route = call_user_func($this->validator, $route);
        $pattern = $route->auto($this, false);
        if (! isset($pattern)) {
            return null;
        }
        return new NestedMatcher($this, $this->validator, $pattern);
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
}
