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
    
    public function __construct($defaultPath)
    {
        $this->addPath('/', $defaultPath);
    }
    
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
    
    public function findIn($asset, $namespace)
    {
        if (! isset($this->paths[$namespace])) {
            return null;
        }
        foreach ($this->paths[$namespace] as $path) {
            $file = $path['path'] . $asset;
            if (file_exists($file)) {
                return $file;
            }
        }
        return null;
    }

    public function fromArray(array $route)
    {
        $parameters = array_merge(
            explode('/', $route['asset']),
            $route['parameters']
        );
        return new AssetRoute($this, $parameters);
    }

    public function fromString($routeString)
    {
        return new AssetRoute($this, explode('/', substr($routeString, 6)));
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
