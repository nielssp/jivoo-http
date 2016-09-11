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
    
    private $path;
    
    private $domain;
    
    private $httpOnly;
    
    private $secure;
    
    /**
     * Construct cookie pool.
     *
     * @param string $path Default cookie path.
     * @param string $domain Default cookie domain.
     * @param bool $httpOnly Default HttpOnly flag.
     * @param bool $secure Default Secure flag.
     */
    public function __construct($path = '/', $domain = '', $httpOnly = true, $secure = false)
    {
        $this->path = $path;
        $this->domain = $domain;
        $this->httpOnly = $httpOnly;
        $this->secure = $secure;
    }
    
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
            $this->cookies[$cookie->getName()] = $this->setDefaults(
                new MutableCookie($cookie->getName(), $cookie->get())
            );
        }
    }
    
    private function setDefaults(MutableCookie $cookie, $new = false)
    {
        $changed = $cookie->hasChanged() || $new;
        return $cookie->setPath($this->path)
            ->setDomain($this->domain)
            ->setSecure($this->secure)
            ->setHttpOnly($this->httpOnly)
            ->setChanged($changed);
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
            $this->cookies[$name] = $this->setDefaults(new MutableCookie($name), true);
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
