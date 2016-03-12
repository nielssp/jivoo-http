<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Cookie;

/**
 * Description of CookiePool
 */
class CookiePool implements \ArrayAccess, \IteratorAggregate
{
    
    /**
     * @var MutableCookie[]
     */
    private $cookies = [];
    
    /**
     *
     * @param \Jivoo\Http\Cookie\Cookie $cookie
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
                ->setExpiration($cookie->getExpiration());
        } else {
            $this->cookies[$cookie->getName()] = new MutableCookie($cookie->getName(), $cookie->get());
        }
    }
    
    /**
     *
     * @param string $name
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this[$name] != '';
    }

    /**
     *
     * @param string $name
     * @return MutableCookie
     */
    public function offsetGet($name)
    {
        if (! isset($this->cookies[$name])) {
            $this->cookies[$name] = new MutableCookie($name);
        }
        return $this->cookies[$name];
    }

    /**
     *
     * @param string $name
     * @param string $value
     */
    public function offsetSet($name, $value)
    {
        $this[$name]->set($value);
    }

    /**
     *
     * @param string $name
     */
    public function offsetUnset($name)
    {
        $this[$name]->set('');
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->cookies);
    }
}
