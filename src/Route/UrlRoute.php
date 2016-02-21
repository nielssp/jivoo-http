<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Description of UrlRoute
 */
class UrlRoute extends RouteBase
{
    private $url;
    
    public function __construct($url)
    {
        if (strpos($url, '?') === false and strpos($url, '#') === false) {
            $this->url = $url;
        } else {
            $uri = new \Jivoo\Http\Message\Uri($url);
            $query = $uri->getQuery();
            if ($query != '') {
                parse_str($query, $this->query);
                $uri = $uri->withQuery('');
            }
            $fragment = $uri->getFragment();
            if ($fragment != '') {
                $this->fragment = $fragment;
                $uri = $uri->withFragment('');
            }
            $this->url = $uri->__toString();
        }
    }
    
    public function __toString()
    {
        $url = $this->getUrl();
        if (preg_match('/^https?:/', $url) === 1) {
            return $url;
        }
        return 'url:' . $url;
    }

    public function auto(Matcher $matcher, $resource = false)
    {
        throw new RouteException('It is not possible to autoroute a URL');
    }

    public function dispatch(\Jivoo\Http\ActionRequest $request, \Psr\Http\Message\ResponseInterface $response)
    {
        return \Jivoo\Http\Message\Response::redirect($this->getUrl());
    }

    public function getPath($pattern)
    {
        return $this->getUrl();
    }
    
    public function getUrl()
    {
        $url = $this->url;
        $query = http_build_query($this->getQuery());
        $fragment = $this->getFragment();
        if ($query != '') {
            $url .= '?' . $query;
        }
        if ($fragment != '') {
            $url .= '#' . $fragment;
        }
        return $url;
    }
}
