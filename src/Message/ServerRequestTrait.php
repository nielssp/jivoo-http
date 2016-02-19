<?php
// Jivoo HTTP
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * Implements {@see \Psr\Http\Message\ServerRequestInterface}.
 */
trait ServerRequestTrait
{
    
    private $attributes = [];
    
    private $cookies = [];
    
    private $data = [];
    
    private $query = [];
    
    private $server = [];
    
    private $files = [];

    public function getAttribute($name, $default = null)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
        return $default;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getCookieParams()
    {
        return $this->cookies;
    }

    public function getParsedBody()
    {
        return $this->data;
    }

    public function getQueryParams()
    {
        return $this->query;
    }

    public function getServerParams()
    {
        return $this->server;
    }

    public function getUploadedFiles()
    {
        return $this->files;
    }

    public function withAttribute($name, $value)
    {
        $request = clone $this;
        $request->attributes[$name] = $value;
        return $request;
    }

    public function withCookieParams(array $cookies)
    {
        $request = clone $this;
        $request->cookies = $cookies;
        return $request;
    }

    public function withParsedBody($data)
    {
        $request = clone $this;
        $request->data = $data;
        return $request;
    }

    public function withQueryParams(array $query)
    {
        $request = clone $this;
        $request->query = $query;
        return $request;
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        $request = clone $this;
        $request->files = $uploadedFiles;
        return $request;
    }

    public function withoutAttribute($name)
    {
        if (! isset($this->attributes[$name])) {
            return $this;
        }
        $request = clone $this;
        unset($request->attributes[$name]);
        return $request;
    }
}
