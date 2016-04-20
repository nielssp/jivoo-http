<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * URL route. Redirects to a URL.
 */
class UrlRoute extends RouteBase
{
    
    /**
     * @var string
     */
    private $url;
    
    /**
     * Construct URL route.
     *
     * @param string $url URL.
     */
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
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $url = $this->getUrl();
        if (preg_match('/^https?:/', $url) === 1) {
            return $url;
        }
        return 'url:' . $url;
    }

    /**
     * {@inheritdoc}
     */
    public function auto(Matcher $matcher, $resource = false)
    {
        throw new RouteException('It is not possible to autoroute a URL');
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Jivoo\Http\ActionRequest $request, \Psr\Http\Message\ResponseInterface $response)
    {
        return \Jivoo\Http\Message\Response::redirect($this->getUrl());
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($pattern)
    {
        return $this->getUrl();
    }
    
    /**
     * {@inheritdoc}
     */
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
