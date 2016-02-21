<?php
namespace Jivoo\Http;

use Jivoo\TestCase;

class RouterTest extends TestCase
{
    
    public function testValidate()
    {
        $router = new Router();
        $router->addScheme(new Route\UrlScheme());
        
        $url = $router->validate(['url' => 'foo/bar']);
        $this->assertInstanceOf('Jivoo\Http\Route\UrlRoute', $url);
        $this->assertEquals('url:foo/bar', $url);
        
        $url = $router->validate(['url' => 'foo/bar']);
        $this->assertInstanceOf('Jivoo\Http\Route\UrlRoute', $url);
        $this->assertEquals('url:foo/bar', $url);
    }
    
    public function testGetPath()
    {
        $router = new Router();
        $router->addScheme(new Route\UrlScheme());
        $router->match([
            'foo/bar' => 'http://example.com/bar',
            'baz/**' => 'http://example.com/foo',
            'foobar/:test' => 'http://example.com/baz'
        ]);
        
        $this->assertEquals('http://example.com/bar', $router->getPath('http://example.com/bar'));
        $this->assertEquals('http://example.com/foo', $router->getPath('http://example.com/foo'));
    }
    
    public function testPatternMatch()
    {
        $route1 = new Route\UrlRoute('foo');
        $route2 = new Route\UrlRoute('bar');
        
        $router = new Router();
        $router->match('GET *', $route2, 3);
        $router->match('GET foo', $route1);
        $router->match('GET foo/**', $route2, 2);
        
        $match = $router->findMatch(['foo'], 'GET');
        $this->assertInstanceOf('Jivoo\Http\Route\UrlRoute', $match);
        $this->assertEquals('foo', $match->getUrl());
        
        $router->match('bar/:baz', $route2);
        $match = $router->findMatch(['bar', 'foobar'], 'GET');
        $this->assertInstanceOf('Jivoo\Http\Route\UrlRoute', $match);
        $this->assertEquals('bar', $match->getUrl());
        $this->assertEquals(['baz' => 'foobar'], $match->getParameters());
        
        $match = $router->findMatch(['foo', 'baz', 'foobar'], 'GET');
        $this->assertInstanceOf('Jivoo\Http\Route\UrlRoute', $match);
        $this->assertEquals('bar', $match->getUrl());
        $this->assertEquals(['baz', 'foobar'], $match->getParameters());
        
        $router->match('bar/:0/*', $route1);
        $match = $router->findMatch(['bar', 'baz', 'foobar'], 'GET');
        $this->assertInstanceOf('Jivoo\Http\Route\UrlRoute', $match);
        $this->assertEquals('foo', $match->getUrl());
        $this->assertEquals(['baz', 'foobar'], $match->getParameters());
        
        $this->assertNull($router->findMatch(['foo'], 'POST'));
        $this->assertNull($router->findMatch(['baz'], 'GET'));
    }
}
