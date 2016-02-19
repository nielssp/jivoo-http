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
        $response1 = $this->getInstance()->withStatus(404);
        
        $this->assertEquals(404, $response1->getStatusCode());
        $this->assertEquals('Not Found', $response1->getReasonPhrase());
        
        $response2 = $response1->withStatus(417, 'Foo Bar');
        
        $this->assertEquals(417, $response2->getStatusCode());
        $this->assertEquals('Foo Bar', $response2->getReasonPhrase());
    }
    
    public function testRedirect()
    {
        $response1 = Response::redirect('/foo/bar');
        
        $this->assertEquals(Status::SEE_OTHER, $response1->getStatusCode());
        $this->assertEquals('See Other', $response1->getReasonPhrase());
        $this->assertEquals('/foo/bar', $response1->getHeaderLine('Location'));
        
        $response2 = Response::redirect('/foo/bar', true);
        
        $this->assertEquals(Status::MOVED_PERMANENTLY, $response2->getStatusCode());
        $this->assertEquals('Moved Permanently', $response2->getReasonPhrase());
        $this->assertEquals('/foo/bar', $response2->getHeaderLine('Location'));
    }
    
    public function testFile()
    {
        $file = new PhpStream('tests/data/response', 'wb');
        $file->write('foobar');
        $file->close();
        
        $response = Response::file('tests/data/response', 'text/plain');
        $this->assertEquals(Status::OK, $response->getStatusCode());
        $this->assertEquals('text/plain', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('foobar', $response->getBody());
    }
}
