<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Cookie;

/**
 * A mutable cookie.
 */
class MutableCookie implements ResponseCookie
{
    
    /**
     * @var bool
     */
    private $changed = false;
    
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var string
     */
    private $value;
    
    /**
     * @var string
     */
    private $path = '';
    
    /**
     * @var string
     */
    private $domain = '';
    
    /**
     * @var bool
     */
    private $secure = false;
    
    /**
     * @var bool
     */
    private $httpOnly = false;
    
    /**
     * @var \DateTimeInterface|null
     */
    private $expiration;
    
    /**
     * Construct mutable cookie.
     *
     * @param string $name Cookie name.
     * @param string $value Cookie value.
     */
    public function __construct($name, $value = '')
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
    public function hasChanged()
    {
        return $this->changed;
    }
    
    /**
     * Set changed flag.
     *
     * @param bool $changed Changed.
     * @return self Self.
     */
    public function setChanged($changed)
    {
        $this->changed = $changed;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDomain()
    {
        return $this->domain;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isSecure()
    {
        return $this->secure;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getExpiration()
    {
        return $this->expiration;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->value;
    }
    
    /**
     * Set value of cookie.
     *
     * @param string $value
     * @return self Self.
     */
    public function set($value)
    {
        $this->value = $value;
        $this->changed = true;
        return $this;
    }
    
    /**
     * Set path on which cookie is available.
     *
     * @param string $path
     * @return self Self.
     */
    public function setPath($path)
    {
        $this->path = $path;
        $this->changed = true;
        return $this;
    }
    
    /**
     * Set domain on which cookie is available.
     *
     * @param string $domain
     * @return self Self.
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        $this->changed = true;
        return $this;
    }
    
    /**
     * Whether cookie should only be transimitted over HTTPS.
     *
     * @param bool $secure
     * @return self Self.
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
        $this->changed = true;
        return $this;
    }
    
    /**
     * Whether cookie should only be made available over HTTP.
     *
     * @param bool $httpOnly
     * @return self Self.
     */
    public function setHttpOnly($httpOnly)
    {
        $this->httpOnly = $httpOnly;
        $this->changed = true;
        return $this;
    }
    
    /**
     * Set expiration time for cookie. If set to null, the cookie expires at the
     * end of the session.
     *
     * @param \DateTimeInterface|null $expiration
     * @return self Self.
     */
    public function expiresAt($expiration)
    {
        $this->expiration = $expiration;
        $this->changed = true;
        return $this;
    }
    
    /**
     * Set expiration time for cookie.
     *
     * @param \DateInterval|int $time
     * @return self Self.
     */
    public function expiresAfter($time)
    {
        $this->expiration = new \DateTime();
        if (is_int($time)) {
            if ($time < 0) {
                $time = new \DateInterval('PT' . abs($time) . 'S');
                $time->invert = 1;
            } else {
                $time = new \DateInterval('PT' . $time . 'S');
            }
        }
        $this->expiration->add($time);
        $this->changed = true;
        return $this;
    }
}
