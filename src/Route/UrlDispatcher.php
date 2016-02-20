<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Description of UriDispatcher
 */
class UrlDispatcher implements Dispatcher
{

    public function fromArray(array $route)
    {
        return new UrlRoute($route['url']);
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
        return ['url'];
    }

    public function getPrefixes()
    {
        return ['url', 'http', 'https'];
    }
}
