<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * An object that can be used in place of a route, see {@see Router}.
 */
interface HasRoute
{
    /**
     * Get the route.
     *
     * @return string|array|Route|HasRoute A route, see {@see Router}.
     */
    public function getRoute();
}
