<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

/**
 * A server request wrapper for use with {@see Router}.
 */
class ActionRequest extends Message\Message implements \Psr\Http\Message\ServerRequestInterface
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
        
        if (! isset($this->attributes['accepts'])) {
            $this->attributes['accepts'] = [];
            if (isset($this->server['HTTP_ACCEPT'])) {
                $contentTypes = explode(',', $this->server['HTTP_ACCEPT']);
                foreach ($contentTypes as $contentType) {
                    $contentType = explode(';', $contentType);
                    $this->attributes['accepts'][] = trim(strtolower($contentType[0]));
                }
            }
        }

        if (! isset($this->attributes['encodings'])) {
            $this->attributes['encodings'] = [];
            if (isset($this->server['HTTP_ACCEPT_ENCODING'])) {
                $acceptEncodings = explode(',', $this->server['HTTP_ACCEPT_ENCODING']);
                foreach ($acceptEncodings as $encoding) {
                    $this->attributes['encodings'][] = trim(strtolower($encoding));
                }
            }
        }
    }
    
    /**
     * Whether or not the client accepts the specified type. If the type is
     * omitted then a list of acceptable types is returned.
     *
     * @param string $type MIME type.
     * @return bool|string[] True if client accepts provided type, false otherwise.
     * List of accepted MIME types if type parameter omitted.
     */
    public function accepts($type = null)
    {
        if (!isset($type)) {
            return $this->attributes['accepts'];
        }
        return in_array($type, $this->attributes['accepts']);
    }

    /**
     * Whether or not the client accepts the specified encoding. If the type is
     * omitted then a list of acceptable encodings is returned.
     *
     * @param string $encoding Encoding.
     * @return bool|string[] True if client accepts provided encoding, false otherwise.
     * List of accepted encodings if type parameter omitted.
     */
    public function acceptsEncoding($encoding = null)
    {
        if (!isset($encoding)) {
            return $this->attributes['encodings'];
        }
        return in_array($encoding, $this->attributes['encodings']);
    }


    /**
     * Whether or not the current request method is GET.
     *
     * @return bool True if GET, false if not.
     */
    public function isGet()
    {
        return $this->method == 'GET';
    }

    /**
     * Whether or not the current request method is POST.
     *
     * @return bool True if POST, false if not.
     */
    public function isPost()
    {
        return $this->method == 'POST';
    }

    /**
     * Whether or not the current request method is PATCH.
     *
     * @return bool True if PATCH, false if not.
     */
    public function isPatch()
    {
        return $this->method == 'PATCH';
    }

    /**
     * Whether or not the current request method is DELETE.
     *
     * @return bool True if DELETE, false if not.
     */
    public function isDelete()
    {
        return $this->method == 'DELETE';
    }

    /**
     * Whether or not the current request method is PUT.
     *
     * @return bool True if PUT, false if not.
     */
    public function isPut()
    {
        return $this->method == 'PUT';
    }
 
    /**
     * Whether or not the current request was made with AJAX.
     *
     * @return bool True if it is, false otherwise.
     */
    public function isAjax()
    {
        return isset($this->server['HTTP_X_REQUESTED_WITH'])
            and $this->server['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
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
        $path = ltrim($path, '/');
        if ($path == '') {
            return [];
        }
        return explode('/', $path);
    }
}
