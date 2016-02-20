<?php

// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Description of UrlRoute
 */
class UrlRoute implements Route
{
    private $url;
    
    private $query = null;
    
    private $fragment = null;
    
    public function __construct($url)
    {
        $this->url = $url;
    }
    
    public function __toString()
    {
        
        if (preg_match('/^https?:/', $this->url) === 1) {
            return $this->url;
        }
        return 'url:' . $this->url;
    }

    public function auto(Matcher $matcher, $resource = false)
    {
    }

    public function dispatch(\Jivoo\Http\ActionRequest $request, \Psr\Http\Message\ResponseInterface $response)
    {
        return \Jivoo\Http\Message\Response::redirect($this->url);
    }

    public function getParameters()
    {
        return [];
    }

    public function getPath($pattern)
    {
        return $this->url;
    }
    
    private function parse()
    {
        $url = parse_url($this->url);
        $this->fragment = '';
        $this->query = [];
        if (isset($url['query'])) {
            parse_str($url['query'], $this->query);
        }
        if (isset($url['fragment'])) {
            $this->fragment = $url['fragment'];
        }
        
    }

    public function getFragment()
    {
        if (! isset($this->fragment)) {
            $this->parse();
        }
        return $this->fragment;
    }

    public function getKey()
    {
        return $this->__toString();
    }

    public function getQuery()
    {
        if (! isset($this->query)) {
            $this->parse();
        }
        return $this->query;
    }
}
