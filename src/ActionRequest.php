<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

/**
 * A server request wrapper for use with {@see Router}.
 */
class ActionRequest extends Message\Message
{
    
    use Message\RequestTrait, Message\ServerRequestTrait;
    
    public function __construct(\Psr\Http\Message\ServerRequestInterface $request)
    {
        parent::__construct($request->getBody());
        foreach ($request->getHeaders() as $name => $value) {
            $this->setHeader($name, $value);
        }
        $this->protocolVersion = $request->getProtocolVersion();
        $this->method = $request->getMethod();
        $this->requestTarget = $request->getRequestTarget();
        $this->uri = $request->getUri();
        $this->attributes = $request->getAttributes();
        $this->cookies = $request->getCookieParams();
        $this->data = $request->getParsedBody();
        $this->query = $request->getQueryParams();
        $this->server = $request->getServerParams();
        $this->files = $request->getUploadedFiles();
        if (! isset($this->attributes['path'])) {
            $this->attributes['path'] = self::findPath($this);
        }
    }
    
    public static function findPath(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $server = $request->getServerParams();
        $path = $request->getUri()->getPath();
        if (isset($server['SCRIPT_NAME'])) {
            $scriptName = $server['SCRIPT_NAME'];
            if ($scriptName != '/') {
                $length = strlen($scriptName);
                if (substr($path, 0, $length) == $scriptName) {
                    $path = substr($path, $length);
                }
            }
        }
        return ltrim($path, '/');
    }
}
