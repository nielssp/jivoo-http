<?php
namespace Jivoo\Http\Route;

use Jivoo\TestCase;

class NestedMatcherTest extends TestCase
{
    public function testNesting()
    {
        $route1 = new UrlRoute('foo');
        $route2 = new UrlRoute('bar');
        $route3 = new UrlRoute('baz');
        $route4 = new UrlRoute('bazbar');
        
        $router = new \Jivoo\Http\Router();
        $nested1 = $router->match('foo', $route1);
        $nested2 = $nested1->match('bar', $route2);
        $nested2->error($route4);
        $nested2->match(['baz' => $route3]);
        
        $match = $router->findMatch(['foo'], 'GET');
        $this->assertInstanceOf('Jivoo\Http\Route\UrlRoute', $match);
        $this->assertEquals('foo', $match->getUrl());
        
        $match = $router->findMatch(['foo', 'bar'], 'GET');
        $this->assertInstanceOf('Jivoo\Http\Route\UrlRoute', $match);
        $this->assertEquals('bar', $match->getUrl());
        
        $match = $router->findMatch(['foo', 'bar', 'baz'], 'GET');
        $this->assertInstanceOf('Jivoo\Http\Route\UrlRoute', $match);
        $this->assertEquals('baz', $match->getUrl());
        
        $match = $router->findMatch(['foo', 'bar', 'ba'], 'GET');
        $this->assertInstanceOf('Jivoo\Http\Route\UrlRoute', $match);
        $this->assertEquals('bazbar', $match->getUrl());
        
        $match = $router->findMatch(['foo', 'bar', 'baz', 'foo'], 'GET');
        $this->assertInstanceOf('Jivoo\Http\Route\UrlRoute', $match);
        $this->assertEquals('bazbar', $match->getUrl());
    }
}
