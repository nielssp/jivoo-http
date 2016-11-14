<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Route;

/**
 * Asset routing.
 */
class AssetScheme implements Scheme
{
    
    /**
     * @var array
     */
    private $paths = [];
    
    /**
     * @var callable|null
     */
    private $errorHandler;
    
    /**
     * @var \Mimey\MimeTypes
     */
    private $mimeTypes;
    
    /**
     * @var bool
     */
    private $appendMtime;
    
    /**
     * Construct asset scheme.
     *
     * @param string $defaultPath Default asset-path.
     * @param callable|null $errorHandler A request handler. Called when an asset
     * does not exist.
     */
    public function __construct($defaultPath, $errorHandler = null, $appendMtime = false)
    {
        $this->mimeTypes = new \Mimey\MimeTypes();
        $this->addPath('/', $defaultPath);
        $this->errorHandler = $errorHandler;
        $this->appendMtime = $appendMtime;
    }
    
    /**
     * Create the error response.
     *
     * @param \Jivoo\Http\ActionRequest $request Request.
     * @param \Psr\Http\Message\ResponseInterface $response Response.
     * @return \Psr\Http\Message\ResponseInterface Error response.
     */
    public function handleError(\Jivoo\Http\ActionRequest $request, \Psr\Http\Message\ResponseInterface $response)
    {
        if (isset($this->errorHandler)) {
            return call_user_func($this->errorHandler, $request, $response);
        }
        return $response->withBody(new \Jivoo\Http\Message\StringStream('Asset not found'))
            ->withStatus(\Jivoo\Http\Message\Status::NOT_FOUND);
    }
    
    /**
     * Get MIME type of a file.
     * @param string $fileName File name or path.
     * @return string A MIME type.
     */
    public function getMimeType($fileName)
    {
        return $this->mimeTypes->getMimeType(\Jivoo\Utilities::getFileExtension($fileName));
    }
    
    /**
     * @return bool Whether the assets mtime is appended to the url.
     */
    public function getAppendMtime()
    {
        return $this->appendMtime;
    }
    
    /**
     * Add an asset path.
     *
     * @param string $namespace Asset namespace.
     * @param string $path Path.
     * @param int $priority Path priority.
     */
    public function addPath($namespace, $path, $priority = 5)
    {
        if ($path != '') {
            $path = rtrim($path, '/') . '/';
        }
        $namespace = '/' . trim($namespace, '/');
        
        if (! isset($this->paths[$namespace])) {
            $this->paths[$namespace] = [];
        }
        $this->paths[$namespace][] = [
            'path' => $path,
            'priority' => $priority
        ];
        usort($this->paths[$namespace], ['Jivoo\Utilities', 'prioritySorter']);
    }
    
    /**
     * Find an asset.
     *
     * @param string $asset Asset name.
     * @return string|null Asset path on server, or null if not found.
     */
    public function find($asset)
    {
        $asset = trim($asset, '/');
        $namespace = '/';
        while (true) {
            $file = $this->findIn($asset, $namespace);
            if (isset($file)) {
                return $file;
            }
            $pos = strpos($asset, '/');
            if ($pos === false) {
                break;
            }
            $namespace = rtrim($namespace, '/') . '/' . substr($asset, 0, $pos);
            $asset = substr($asset, $pos + 1);
        }
        return null;
    }
    
    /**
     * Find an asset in a namsepace.
     *
     * @param string $asset Asset name.
     * @param string $namespace Namespace.
     * @return string|null Asset path on server, or null if not found.
     */
    public function findIn($asset, $namespace)
    {
        if (! isset($this->paths[$namespace])) {
            return null;
        }
        foreach ($this->paths[$namespace] as $path) {
            $file = $path['path'] . $asset;
            if (is_file($file)) {
                return $file;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function fromArray(array $route)
    {
        $parameters = array_merge(
            explode('/', $route['asset']),
            $route['parameters']
        );
        return new AssetRoute($this, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function fromString($routeString)
    {
        return new AssetRoute($this, explode('/', substr($routeString, 6)));
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        return ['asset'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefixes()
    {
        return ['asset'];
    }
}
