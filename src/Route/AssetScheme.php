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
     * @var string
     */
    private $default;
    
    /**
     * @var array
     */
    private $paths = [];
    
    /**
     * @var bool
     */
    private $sorted = true;
    
    public function __construct($defaultPath)
    {
        $this->default = $defaultPath;
    }
    
    public function addPath($prefix, $path, $priority = 5)
    {
        $this->sorted = false;
        $this->paths[] = [
            'prefix' => $prefix,
            'path' => $path,
            'priority' => $priority
        ];
    }

    public function fromArray(array $route)
    {
        $asset = $route['asset'];
        if (count($route['parameters'])) {
            $asset .= '/' . implode('/', $route['parameters']);
        }
        $file = $this->default . '/' . $asset;
        // TODO: look in paths etc.
        return new AssetRoute($asset, $file);
    }

    public function fromString($routeString)
    {
        return $this->fromArray(['asset' => substr($routeString, 6)]);
    }

    public function getKeys()
    {
        return ['asset'];
    }

    public function getPrefixes()
    {
        return ['asset'];
    }
}
