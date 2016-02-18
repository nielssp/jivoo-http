<?php
namespace Jivoo\Http;

use Jivoo\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SapiServerTest extends TestCase
{

    public function testListen()
    {
        $server = $this->getMock('Jivoo\Http\SapiServer', ['serve']);
        
        $response1 = new Message\Response(200);
        $response2 = new Message\Response(200);
        $response3 = new Message\Response(200);
        
        // Middleware 1
        $server->add(function ($request, $response, $next) use ($response1, $response2) {
            $this->assertSame($response2, $response);
            return $response1;
        });
        
        // Middleware 2
        $server->add(function ($request, $response, $next) use ($response2, $response3) {
            $this->assertSame($response3, $response);
            return $next($request, $response2); // Calls middleware 1
        });
        
        // Middleware 3
        $server->add(function ($request, $response, $next) use ($response3) {
            return $next($request, $response3); // Calls middleware 2
        });
        
        $server->expects($this->once())
            ->method('serve')
            ->with($this->equalTo($response1));
        
        $server->listen();
    }
}
