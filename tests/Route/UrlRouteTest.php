<?php
namespace Jivoo\Http\Route;

use Jivoo\Http\Message\Response;
use Jivoo\Http\Message\Status;
use Jivoo\TestCase;

class UrlRouteTest extends TestCase
{
    
    public function testUrlRoute()
    {
        $request = $this->getMockBuilder('Jivoo\Http\ActionRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $ok = new Response(Status::OK);
        
        $route = new UrlRoute('/');
        $this->assertEquals('url:/', $route);
        $this->assertEquals('/', $route->getPath([]));
        $this->assertEquals([], $route->getParameters());
        $this->assertEquals([], $route->getQuery());
        $this->assertEquals('', $route->getFragment());
        
        $response = $route->dispatch($request, $ok);
        $this->assertEquals(Status::SEE_OTHER, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeaderLine('Location'));
        
        $route = new UrlRoute('http://example.com/test?foo=bar#baz');
        $this->assertEquals('http://example.com/test?foo=bar#baz', $route);
        $this->assertEquals('http://example.com/test?foo=bar#baz', $route->getPath([]));
        $this->assertEquals('baz', $route->getFragment());
        $this->assertEquals([], $route->getParameters());
        $this->assertEquals(['foo' => 'bar'], $route->getQuery());
        
        $response = $route->dispatch($request, $ok);
        $this->assertEquals(Status::SEE_OTHER, $response->getStatusCode());
        $this->assertEquals('http://example.com/test?foo=bar#baz', $response->getHeaderLine('Location'));
    }
}
