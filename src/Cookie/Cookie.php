<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Cookie;

/**
 * A cookie recieved as part of a request.
 */
interface Cookie
{
    
    /**
     * Get name of cookie.
     *
     * @return string
     */
    public function getName();
    
    /**
     * Get value of cookie or the empty string if not set.
     *
     * @return string
     */
    public function get();
    
    /**
     * Get value of cookie or the empty string if not set.
     *
     * @return string
     */
    public function __toString();
}
