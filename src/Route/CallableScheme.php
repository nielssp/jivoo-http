<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Callable-based routing.
 */
class CallableScheme implements Scheme
{

    /**
     * {@inheritdoc}
     */
    public function fromArray(array $route)
    {
        return new CallableRoute($route['callable'], $route['parameters']);
    }

    /**
     * {@inheritdoc}
     */
    public function fromString($routeString)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        return ['callable'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefixes()
    {
        return [];
    }
}
