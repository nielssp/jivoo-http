<?php
// Jivoo_HTTP 
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

/**
 * The event of rendering a page.
 */
class RenderEvent extends Event
{
    /**
     * @var array|Linkable|string|null $route The route being followed, see {@see Routing}.
     */
    public $route;
  
    /**
     * @var Response|null The rendered response if any.
     */
    public $response;
  
    /**
     * @var string|null The response body if any.
     */
    public $body;
  
    /**
     * @var bool Set to true to override response body.
     */
    public $overrideBody = false;
  
    /**
     * Construct render event.
     * @param object $sender Sender object.
     * @param array|Linkable|string|null $route The route being followed, see {@see Routing}.
     * @param Response|null The rendered response if any.
     * @param string|null The response body if any.
     */
    public function __construct($sender, $route, Response $response = null, $body = null)
    {
        parent::__construct($sender);
        $this->route = $route;
        $this->response = $response;
        $this->body = $body;
    }
}
