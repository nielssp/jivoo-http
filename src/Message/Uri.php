<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * Description of Uri
 */
class Uri implements \Psr\Http\Message\UriInterface
{
    private $userInfo = '';
    
    private $scheme = '';
    
    private $host = '';
    
    private $port = null;
    
    private $path = '';
    
    private $query = '';
    
    private $fragment = '';
    
    public function __construct($url)
    {
        $components = parse_url($url);
        if (isset($components['scheme'])) {
            $this->scheme = strtolower($components['scheme']);
        }
        if (isset($components['host'])) {
            $this->host = strtolower($components['host']);
        }
        if (isset($components['path'])) {
            $this->path = strtolower($components['path']);
        }
        if (isset($components['port'])) {
            $this->port = $components['port'];
        }
        if (isset($components['user'])) {
            $userInfo = $components['user'];
            if (isset($components['pass'])) {
                $userInfo .= ':' . $components['pass'];
            }
            $this->userInfo = $userInfo;
        }
        if (isset($components['query'])) {
            $this->query = $components['query'];
        }
        if (isset($components['fragment'])) {
            $this->fragment = $components['fragment'];
        }
    }
    
    public function __toString()
    {
        $uri = $this->getScheme();
        if ($uri != '') {
            $uri .= ':';
        }
        $authority = $this->getAuthority();
        if ($authority != '') {
            $uri .= '//' . $authority;
        }
        $path = $this->getPath();
        if ($path != '') {
            if ($path[0] == '/') {
                if ($authority == '' and isset($path[1]) and $path[1] == '/') {
                    $path = '/' . ltrim($path, '/');
                }
            } elseif ($authority != '') {
                $path = '/' . $path;
            }
            $uri .= $path;
        }
        $query = $this->getQuery();
        if ($query != '') {
            $uri .= '?' . $query;
        }
        $fragment = $this->getFragment();
        if ($fragment != '') {
            $uri .= '#' . $fragment;
        }
        return $uri;
    }

    public function getAuthority()
    {
        $authority = $this->getHost();
        if ($authority == '') {
            return '';
        }
        $userInfo = $this->getUserInfo();
        if ($userInfo != '') {
            $authority = $userInfo . '@' . $authority;
        }
        $port = $this->getPort();
        if ($port != '') {
            $authority .= ':' . $port;
        }
        return $authority;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getPort()
    {
        if ($this->scheme == 'http' and $this->port == 80) {
            return '';
        } elseif ($this->scheme == 'https' and $this->port == 443) {
            return '';
        }
        return $this->port;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getUserInfo()
    {
        return $this->userInfo;
    }

    public function withFragment($fragment)
    {
        $uri = clone $this;
        $uri->fragment = urlencode(urldecode($fragment));
        return $uri;
    }

    public function withHost($host)
    {
        $uri = clone $this;
        $uri->host = strtolower($host);
        return $uri;
    }

    public function withPath($path)
    {
        $uri = clone $this;
        $uri->path = strtolower(urlencode(urldecode($path)));
        return $uri;
    }

    public function withPort($port)
    {
        \Jivoo\Assume::that($port > 0 and $port < 65535);
        $uri = clone $this;
        $uri->port = $port;
        return $uri;
    }

    public function withQuery($query)
    {
        $uri = clone $this;
        $uri->query = urlencode(urldecode($query));
        return $uri;
    }

    public function withScheme($scheme)
    {
        $uri = clone $this;
        $uri->scheme = strtolower($scheme);
        return $uri;
    }

    public function withUserInfo($user, $password = null)
    {
        if (isset($password)) {
            $user .= ':' . $password;
        }
        $uri = clone $this;
        $uri->userInfo = $user;
        return $uri;
    }
}
