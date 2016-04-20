<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Path-based route. Redirects to the path.
 */
class PathRoute extends RouteBase
{
    /**
     * @var string[]
     */
    private $path;
    
    /**
     * Construct path route.
     *
     * @param string[] $path Path array.
     * @param string[] $query Query parameters.
     * @param string $fragment Fragment.
     */
    public function __construct(array $path, array $query = [], $fragment = '')
    {
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'path:' . implode('/', $this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function auto(Matcher $matcher, $resource = false)
    {
        throw new RouteException('It is not possible to autoroute a path');
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Jivoo\Http\ActionRequest $request, \Psr\Http\Message\ResponseInterface $response)
    {
        $location = new Message\Uri($request->pathToString($this->path));
        $location = $location->withQuery(http_build_query($this->query))
            ->withFragment($this->fragment);
        return Message\Response::redirect($location, false);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($pattern)
    {
        return $this->path;
    }
}
