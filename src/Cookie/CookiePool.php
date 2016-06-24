<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Cookie;

/**
 * A mutable collection of cookies.
 */
class CookiePool implements \ArrayAccess, \IteratorAggregate
{
    
    /**
     * @var MutableCookie[]
     */
    private $cookies = [];
    
    /**
     * Add a cookie to the pool.
     *
     * @param Cookie $cookie A cookie.
     */
    public function add(Cookie $cookie)
    {
        if ($cookie instanceof MutableCookie) {
            $this->cookies[$cookie->getName()] = $cookie;
        } elseif ($cookie instanceof ResponseCookie) {
            $this[$cookie->getName()]->set($cookie->get())
                ->setPath($cookie->getPath())
                ->setDomain($cookie->getDomain())
                ->setSecure($cookie->isSecure())
                ->setHttpOnly($cookie->isHttpOnly())
                ->expiresAt($cookie->getExpiration());
        } else {
            $this->cookies[$cookie->getName()] = new MutableCookie($cookie->getName(), $cookie->get());
        }
    }
    
    /**
     * Whether a cookie exists and is non-empty.
     *
     * @param string $name Cookie name.
     * @return bool True if cookie has a non-empty value.
     */
    public function offsetExists($name)
    {
        return $this[$name] != '';
    }

    /**
     * Get a cookie.
     *
     * @param string $name Cookie name.
     * @return MutableCookie A mutable cookie.
     */
    public function offsetGet($name)
    {
        if (! isset($this->cookies[$name])) {
            $this->cookies[$name] = new MutableCookie($name);
        }
        return $this->cookies[$name];
    }

    /**
     * Set the value of a cookie.
     *
     * @param string $name Cookie name.
     * @param string $value Cookie value.
     */
    public function offsetSet($name, $value)
    {
        $this[$name]->set($value);
    }

    /**
     * Unset a cookie, i.e. set its value to the empty string.
     *
     * @param string $name Cookie name.
     */
    public function offsetUnset($name)
    {
        $this[$name]->set('');
    }

    /**
     * Create an iterator of the cookies in the collection.
     *
     * @return Iterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->cookies);
    }
}
