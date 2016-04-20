<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Callable-based route.
 */
class CallableRoute extends RouteBase
{
    
    /**
     * @var callable
     */
    private $callable;
    
    /**
     * Construct callable route.
     *
     * @param \Jivoo\Http\Route\callable $callable Dispatch function.
     * @param string[] $parameters Route parameters.
     */
    public function __construct(callable $callable, $parameters)
    {
        $this->callable = $callable;
        $this->parameters = $parameters;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        throw new RouteException('Cannot create string from a callable route');
    }

    /**
     * {@inheritdoc}
     */
    public function auto(Matcher $matcher, $resource = false)
    {
        throw new RouteException('It is not possible to autoroute a callable route');
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Jivoo\Http\ActionRequest $request, \Psr\Http\Message\ResponseInterface $response)
    {
        return call_user_func($this->callable, $request, $response, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($pattern)
    {
        return $this->getUrl();
    }
}
