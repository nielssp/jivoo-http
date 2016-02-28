<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * URL-based routes.
 */
class CallableScheme implements Scheme
{

    public function fromArray(array $route)
    {
        return new CallableRoute($route['callable']);
    }

    public function fromString($routeString)
    {
        if (strncmp($routeString, 'url:', 4) === 0) {
            return new UrlRoute(substr($routeString, 4));
        }
        return new UrlRoute($routeString);
    }

    public function getKeys()
    {
        return ['callable'];
    }

    public function getPrefixes()
    {
        return ['callable'];
    }
}
