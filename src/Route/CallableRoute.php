<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Description of UrlRoute
 */
class CallableRoute extends RouteBase
{
    private $callable;
    
    public function __construct(callable $callable, $parameters)
    {
        $this->callable = $callable;
        $this->parameters = $parameters;
    }
    
    public function __toString()
    {
        throw new RouteException('Cannot create string from a callable route');
    }

    public function auto(Matcher $matcher, $resource = false)
    {
        throw new RouteException('It is not possible to autoroute a callable route');
    }

    public function dispatch(\Jivoo\Http\ActionRequest $request, \Psr\Http\Message\ResponseInterface $response)
    {
        return call_user_func($this->callable, $request, $response, $this->parameters);
    }

    public function getPath($pattern)
    {
        return $this->getUrl();
    }
}
