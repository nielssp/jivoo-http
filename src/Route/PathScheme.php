<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Path-based routes.
 */
class PathScheme implements Scheme
{

    public function fromArray(array $route)
    {
        $path = $route['path'];
        if (is_string($path)) {
            $path = explode('/', trim($path, '/'));
        }
        return new PathRoute($path);
    }

    public function fromString($routeString)
    {
        $path = substr($routeString, 5);
        return $this->fromArray(['path' => $path]);
    }

    public function getKeys()
    {
        return ['path'];
    }

    public function getPrefixes()
    {
        return ['path'];
    }
}