<?php
namespace Jivoo\Http\Message;

class ServerRequestTraitTest extends \Jivoo\TestCase
{
    
    protected function getInstance()
    {
        return Request::create('?test');
    }
    
    public function testAttributes()
    {
        $request1 = $this->getInstance();
        
        $this->assertNull($request1->getAttribute('foo'));
        $this->assertEquals([], $request1->getAttributes());
        $this->assertEquals('bar', $request1->getAttribute('foo', 'bar'));
        
        $request2 = $request1->withAttribute('foo', 'bar');
        $this->assertEquals('bar', $request2->getAttribute('foo'));
        
        $request3 = $request2->withoutAttribute('foo');
        $this->assertNull($request3->getAttribute('foo'));
        
        $request4 = $request3->withoutAttribute('foo');
        $this->assertNull($request4->getAttribute('foo'));
    }
    
    public function testGettersAndSetters()
    {
        $file = new UploadedFile([
            'tmp_name' => 'foo',
            'name' => 'foo',
            'type' => 'text/plain',
            'size' => 0,
            'error' => 0
        ]);
        
        $request1 = $this->getInstance()
            ->withCookieParams(['foo' => 'bar'])
            ->withParsedBody(['baz' => 'foobar'])
            ->withQueryParams(['bar' => 'foo'])
            ->withUploadedFiles(['foo' => $file]);
        
        $this->assertEquals(['foo' => 'bar'], $request1->getCookieParams());
        $this->assertEquals(['baz' => 'foobar'], $request1->getParsedBody());
        $this->assertEquals(['bar' => 'foo'], $request1->getQueryParams());
        $this->assertEquals([], $request1->getServerParams());
        $this->assertEquals(['foo' => $file], $request1->getUploadedFiles());
    }
}
