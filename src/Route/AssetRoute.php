<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Asset route.
 */
class AssetRoute extends RouteBase
{
    
    /**
     * @var AssetScheme
     */
    private $scheme;

    public function __construct(AssetScheme $scheme, array $parameters)
    {
        $this->scheme = $scheme;
        $this->parameters = $parameters;
    }
    
    public function __toString()
    {
        return 'asset:' . implode('/', $this->parameters);
    }

    public function auto(Matcher $matcher, $resource = false)
    {
        throw new RouteException('It is not possible to autoroute a single asset');
    }

    public function dispatch(\Jivoo\Http\ActionRequest $request, \Psr\Http\Message\ResponseInterface $response)
    {
        $file = $this->scheme->find(implode('/', $this->parameters));
        if (! isset($file)) {
            return; // TODO: how to handle 'not found'?
        }
        $type = null; // TODO: Find MIME type
        return \Jivoo\Http\Message\Response::file($file, $type);
    }

    public function getPath($pattern)
    {
        if (isset($pattern)) {
            return RouteBase::insertParameters($pattern, $this->parameters);
        }
        return $this->parameters;
    }
}
