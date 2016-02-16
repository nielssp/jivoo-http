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
}
