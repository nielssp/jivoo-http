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

    /**
     * Construct asset route.
     *
     * @param \Jivoo\Http\Route\AssetScheme $scheme Asset scheme.
     * @param string[] $parameters Route parameters.
     */
    public function __construct(AssetScheme $scheme, array $parameters)
    {
        $this->scheme = $scheme;
        $this->parameters = $parameters;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'asset:' . implode('/', $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function auto(Matcher $matcher, $resource = false)
    {
        throw new RouteException('It is not possible to autoroute a single asset');
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Jivoo\Http\ActionRequest $request, \Psr\Http\Message\ResponseInterface $response)
    {
        $file = $this->scheme->find(implode('/', $this->parameters));
        if (! isset($file)) {
            return $this->scheme->handleError($request, $response);
        }
        $type = $this->scheme->getMimeType($file);
        $response = \Jivoo\Http\Message\Response::file($file, $type)->cached();
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($pattern)
    {
        if (isset($pattern)) {
            return RouteBase::insertParameters($pattern, $this->parameters);
        }
        return $this->parameters;
    }
}
