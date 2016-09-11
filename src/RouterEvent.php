<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

use Jivoo\Event;
use Jivoo\Http\Route\Route;
use Psr\Http\Message\ResponseInterface;

/**
 * Events triggered by {@see Router}.
 */
class RouterEvent extends Event
{
    
    /**
     * The current route.
     *
     * @var Route
     */
    public $route;
    
    /**
     * The current request.
     *
     * @var ActionRequest
     */
    public $request;
    
    /**
     * The current response.
     *
     * @var ResponseInterface
     */
    public $response;
    
    public function __construct(
        Router $sender = null,
        Route $route = null,
        ActionRequest $request = null,
        ResponseInterface $response = null
    ) {
        parent::__construct($sender);
        $this->route = $route;
        $this->request = $request;
        $this->response = $response;
    }
}
