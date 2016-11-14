<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Cookie;

/**
 * A request cookie.
 */
class RequestCookie implements Cookie
{
    
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var string
     */
    private $value;
    
    /**
     * Construct request cookie.
     *
     * @param string $name Cookie name.
     * @param string $value Cookie value.
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->value;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->value;
    }
}
