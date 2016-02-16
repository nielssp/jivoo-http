<?php
namespace Jivoo\Http\Message;

class ResponseTest extends MessageTest
{
    protected function getInstance()
    {
        return new Response(200, new StringStream(''));
    }
    
    public function testStatus()
    {
        $request1 = $this->getInstance()->withStatus(404);
        
        $this->assertEquals(404, $request1->getStatusCode());
        $this->assertEquals('Not Found', $request1->getReasonPhrase());
        
        $request2 = $request1->withStatus(417, 'Foo Bar');
        
        $this->assertEquals(417, $request2->getStatusCode());
        $this->assertEquals('Foo Bar', $request2->getReasonPhrase());
    }
    
    public function testRedirect()
    {
        $request1 = Response::redirect('/foo/bar');
        
        $this->assertEquals(Status::SEE_OTHER, $request1->getStatusCode());
        $this->assertEquals('See Other', $request1->getReasonPhrase());
        $this->assertEquals('/foo/bar', $request1->getHeaderLine('Location'));
        
        $request2 = Response::redirect('/foo/bar', true);
        
        $this->assertEquals(Status::MOVED_PERMANENTLY, $request2->getStatusCode());
        $this->assertEquals('Moved Permanently', $request2->getReasonPhrase());
        $this->assertEquals('/foo/bar', $request2->getHeaderLine('Location'));
    }
}
