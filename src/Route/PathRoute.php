<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Path-based route
 */
class PathRoute extends RouteBase
{
    private $path;
    
    public function __construct(array $path, array $query = [], $fragment = '')
    {
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
    }
    
    public function __toString()
    {
        return 'path:' . implode('/', $this->path);
    }

    public function auto(Matcher $matcher, $resource = false)
    {
        throw new RouteException('It is not possible to autoroute a path');
    }

    public function dispatch(\Jivoo\Http\ActionRequest $request, \Psr\Http\Message\ResponseInterface $response)
    {
        $location = new Message\Uri($request->pathToString($this->path));
        $location = $location->withQuery(http_build_query($this->query))
            ->withFragment($this->fragment);
        return Message\Response::redirect($location, false);
    }

    public function getPath($pattern)
    {
        return $this->path;
    }
}
