<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

/**
 * Request
 */
abstract class ActionRequest implements \Psr\Http\Message\ServerRequestInterface
{
    
    public function __construct(\Psr\Http\Message\ServerRequestInterface $request)
    {
        
    }
}
