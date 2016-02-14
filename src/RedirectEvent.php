<?php
// Jivoo_HTTP 
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

/**
 * A redirect event.
 */
class RedirectEvent extends Event
{
    /**
     * @var array|Linkable|string|null $route A route, see {@see Routing}.
     */
    public $route;
  
    /**
     * @var bool Whether it is a permanent (true) or temporary (false) redirect.
     */
    public $moved;
  
    /**
     * Construct redirect event.
     * @param object $sender Sender object.
     * @param array|Linkable|string|null $route A route, see {@see Routing}.
     * @param bool $movied Whether it is a permanent (true) or temporary (false) redirect.
     */
    public function __construct($sender, $route, $moved)
    {
        parent::__construct($sender);
        $this->route = $route;
        $this->moved = $moved;
    }
}
