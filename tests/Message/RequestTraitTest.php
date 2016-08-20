<?php
namespace Jivoo\Http\Message;

class RequestTraitTest extends \Jivoo\TestCase
{
    
    protected function getInstance()
    {
        return Request::create('?test');
    }
    
    public function testMethod()
    {
        $request1 = $this->getInstance();
        
        $this->assertEquals('GET', $request1->getMethod());
        $request2 = $request1->withMethod('POST');
        $this->assertEquals('POST', $request2->getMethod());
        $this->assertEquals('GET', $request1->getMethod());
    }
    
    public function testRequestTarget()
    {
        $request1 = $this->getInstance();
        
        $this->assertEquals('/?test', $request1->getRequestTarget());
        
        $request2 = $request1->withUri(new Uri('/foo'));
        
        $this->assertEquals('/foo', $request2->getRequestTarget());
        
        $request3 = $request2->withRequestTarget('*');
        
        $this->assertEquals('*', $request3->getRequestTarget());
    }
    
    public function testUri()
    {
        $request1 = $this->getInstance();
        
        $this->assertEquals('test', $request1->getUri()->getQuery());
        $this->assertEquals('', $request1->getHeaderLine('host'));
        
        $request2 = $request1->withUri(new Uri('http://example.com/foo'));
        
        $this->assertEquals('/foo', $request2->getUri()->getPath());
        $this->assertEquals('example.com', $request2->getUri()->getHost());
        $this->assertEquals('example.com', $request2->getHeaderLine('host'));
        
        $request3 = $request2->withUri(new Uri('http://example.org/bar'), true);
        
        $this->assertEquals('/bar', $request3->getUri()->getPath());
        $this->assertEquals('example.com', $request3->getHeaderLine('host'));
    }
}
