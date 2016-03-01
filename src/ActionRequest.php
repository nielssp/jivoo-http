<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

/**
 * A server request wrapper for use with {@see Router}.
 * @property-read string[] $path Routable path array.
 * @property-read string $basPath Request base path, .e.g. '/foo/' in
 * '/foo/index.php/bar'.
 * @property-read string $scriptName Entry script name, e.g. 'index.php' in
 * '/foo/index.php/bar'.
 * @property-read bool $rewrite Whether HTTP rewrite is enabled.
 * @property-read string $method Request method.
 * @property-read Message\Uri $uri Request URI.
 * @property-read array $data POST/PUT/PATCH data.
 * @property-read array $query Request query.
 */
class ActionRequest extends Message\Message implements \Psr\Http\Message\ServerRequestInterface
{
    
    use Message\RequestTrait, Message\ServerRequestTrait;
    
    /**
     * Construct action request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request to wrap.
     */
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
        
        if (isset($this->server['SCRIPT_NAME'])) {
            $this->attributes['basePath'] = dirname($this->server['SCRIPT_NAME']);
            $this->attributes['scriptName'] = basename($this->server['SCRIPT_NAME']);
        } else {
            $this->attributes['basePath'] = '/';
            $this->attributes['scriptName'] = 'index.php';
        }
        
        if (! isset($this->attributes['path'])) {
            $this->attributes['path'] = self::findPath($this);
        }
        
        if (! isset($this->attributes['rewrite'])) {
            $this->attributes['rewrite'] = false;
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
     * Get value of property.
     * @param string $property Property name.
     * @return mixed Value of property.
     * @throws \Jivoo\InvalidPropertyException If the property is undefined.
     */
    public function __get($property)
    {
        switch ($property) {
            case 'path':
            case 'basePath':
            case 'scriptName':
            case 'rewrite':
                return $this->getAttribute($property);
            case 'method':
            case 'uri':
            case 'data':
            case 'query':
                return $this->$property;
        }
        throw new \Jivoo\InvalidPropertyException('Undefined property: ' . $property);
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
    
    /**
     * Convert path array to a string.
     *
     * @param string|string[] $path Path array or absolute url.
     * @param bool $rewrite Whether to force removal of script name from path.
     * @return string Path string.
     */
    public function pathToString($path, $rewrite = false)
    {
        if (is_string($path)) {
            return $path;
        }
        $str = $this->basePath;
        if ($str == '/') {
            $str = '';
        }
        if (! ($this->rewrite or $rewrite)) {
            $str .= '/' . $this->scriptName;
        }
        $str .= '/' . implode('/', array_map('urlencode', $path));
        $str = rtrim($str, '/');
        if ($str == '') {
            return '/';
        }
        return $str;
    }
    
    public static function findPath(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $server = $request->getServerParams();
        $path = $request->getUri()->getPath();
        if (isset($server['SCRIPT_NAME'])) {
            $basePath = dirname($server['SCRIPT_NAME']);
            if ($basePath != '/') {
                $length = strlen($basePath);
                if (substr($path, 0, $length) == $basePath) {
                    $path = substr($path, $length);
                }
            }
        }
        if ($path == '/' or $path == '') {
            return [];
        }
        if ($path[0] == '/') {
            $path = substr($path, 1);
        }
        return explode('/', $path);
    }
}
