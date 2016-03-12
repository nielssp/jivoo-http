<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * A cookie.
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
     * Get value of cookie if set.
     *
     * @param string|null $value
     */
    public function get($value);
    
    /**
     * Set value of cookie.
     *
     * @param string $value
     */
    public function set($value);
    
    /**
     * Set path on which cookie is available.
     *
     * @param string $path
     */
    public function setPath($path);
    
    /**
     * Set domain on which cookie is available.
     *
     * @param string $domain
     */
    public function setDomain($domain);
    
    /**
     * Whether cookie should only be transimitted over HTTPS.
     *
     * @param bool $secure
     */
    public function setSecure($secure);
    
    /**
     * Whether cookie should only be made available over HTTP.
     *
     * @param bool $httpOnly
     */
    public function setHttpOnly($httpOnly);
    
    /**
     * Set expiration time for cookie.
     *
     * @param \DateTimeInterface $expiration
     */
    public function expiresAt($expiration);
    
    /**
     * Set expiration time for cookie.
     *
     * @param \DateInterval|int $time
     */
    public function expiresAfter($time);
}
