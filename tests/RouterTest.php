<?php
namespace Jivoo\Http;

use Jivoo\TestCase;

class RouterTest extends TestCase
{
    
    public function testValidate()
    {
        $router = new Router();
        $router->addScheme(new Route\UrlScheme());
        $router->addScheme(new Route\CallableScheme());
        
        $url = $router->validate(['url' => 'foo/bar']);
        $this->assertInstanceOf('Jivoo\Http\Route\UrlRoute', $url);
        $this->assertEquals('url:foo/bar', $url);
        
        $callable = $router->validate([
            'callable' => function () {
            },
            'parameters' => ['baz'],
            'foo',
            'bar'
        ]);
        $this->assertInstanceOf('Jivoo\Http\Route\CallableRoute', $callable);
        $this->assertEquals(['baz', 'foo', 'bar'], $callable->getParameters());
        
        $router->root('http://example.com');
        $this->assertEquals('http://example.com', $router->validate(''));
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
    
    public function testRedirect()
    {
        $router = new Router();
        $router->addScheme(new Route\UrlScheme());
        $router->match('foo', 'http://example.com');

        $request = Message\Request::create('/index.php/foo')
            ->withServerParams(['SCRIPT_NAME' => '/index.php']);
        
        $router($request, new Message\Response(Message\Status::OK));
        
        $response = $router->redirect('http://example.com/foo');
        $this->assertEquals(Message\Status::SEE_OTHER, $response->getStatusCode());
        $this->assertEquals('http://example.com/foo', $response->getHeaderLine('Location'));
    }
    
    public function testInvoke()
    {
        $router = new Router();
        $router->enableRewrite();
        $router->addScheme(new Route\UrlScheme());
        $router->match('foo/bar', 'http://example.com/bar');
        
        $request1 = Message\Request::create('/foo/bar');
        $response1 = new Message\Response(Message\Status::OK);
        
        $response2 = $router($request1, $response1);
        $this->assertEquals(Message\Status::SEE_OTHER, $response2->getStatusCode());
        $this->assertEquals('http://example.com/bar', $response2->getHeaderLine('Location'));
        
        $request2 = Message\Request::create('/foo');
        $this->assertThrows('Jivoo\Http\Route\RouteException', function () use ($router, $request2, $response1) {
            $router($request2, $response1);
        });
        
        // Test removal of trailing slash
        $request3 = Message\Request::create('/foo/');
        $response3 = $router($request3, $response1);
        $this->assertEquals(Message\Status::MOVED_PERMANENTLY, $response3->getStatusCode());
        $this->assertEquals('/foo', $response3->getHeaderLine('Location'));
        
        $router->disableRewrite();
        
        // Test index.php redirects
        $request4 = Message\Request::create('/foo/bar')->withServerParams([
            'SCRIPT_NAME' => '/index.php'
        ]);
        $response4 = $router($request4, $response1);
        $this->assertEquals(Message\Status::MOVED_PERMANENTLY, $response4->getStatusCode());
        $this->assertEquals('/index.php/foo/bar', $response4->getHeaderLine('Location'));
        
        // Test index.php removal from path
        $request5 = Message\Request::create('/index.php/foo/bar')->withServerParams([
            'SCRIPT_NAME' => '/index.php'
        ]);
        $response5 = $router($request5, $response1);
        $this->assertEquals(Message\Status::SEE_OTHER, $response5->getStatusCode());
        $this->assertEquals('http://example.com/bar', $response5->getHeaderLine('Location'));
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
        $this->assertNull($router->findMatch(['baz', 'bar'], 'GET'));
    }
}
