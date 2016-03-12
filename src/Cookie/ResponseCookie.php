<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Cookie;

/**
 * A response cookie.
 */
interface ResponseCookie extends Cookie
{
    
    /**
     * Whether the cookie's value or properties has been changed.
     *
     * @return bool
     */
    public function hasChanged();
    
    /**
     * Get path on which cookie is available.
     *
     * @return string
     */
    public function getPath();
    
    /**
     * Get domain on which cookie is available.
     *
     * @return string
     */
    public function getDomain();
    
    /**
     * Whether cookie should only be transimitted over HTTPS.
     *
     * @return bool
     */
    public function isSecure();
    
    /**
     * Whether cookie should only be made available over HTTP.
     *
     * @return bool
     */
    public function isHttpOnly();
    
    /**
     * Get expiration time for cookie.
     *
     * @return \DateTimeInterface|null
     */
    public function getExpiration();
}
