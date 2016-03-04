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
    private $asset;
    
    private $file;
    
    public function __construct($asset, $file)
    {
        $this->asset = $asset;
        $this->file = $file;
    }
    
    public function __toString()
    {
        return 'asset:' . $this->asset;
    }

    public function auto(Matcher $matcher, $resource = false)
    {
        throw new RouteException('It is not possible to autoroute a single asset');
    }

    public function dispatch(\Jivoo\Http\ActionRequest $request, \Psr\Http\Message\ResponseInterface $response)
    {
        $type = null; // TODO: Find MIME type
        return \Jivoo\Http\Message\Response::file($this->file, $type);
    }

    public function getPath($pattern)
    {
        if (isset($pattern)) {
            return RouteBase::insertParameters($pattern, explode('/', $this->asset));
        }
        return $this->asset;
    }
}
