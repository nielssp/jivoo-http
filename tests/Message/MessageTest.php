<?php
namespace Jivoo\Http\Message;

class MessageTest extends \Jivoo\TestCase
{
    protected function getInstance()
    {
        return new Message(new StringStream(''));
    }
    
    public function testHeaders()
    {
        $message1 = $this->getInstance();
        
        $this->assertFalse($message1->hasHeader('foo'));
        
        $message2 = $message1->withHeader('Foo', 'bar');
        
        $this->assertTrue($message2->hasHeader('foo'));
        $this->assertTrue($message2->hasHeader('Foo'));
        $this->assertTrue($message2->hasHeader('fOO'));
        $this->assertFalse($message1->hasHeader('foo'));
        
        $this->assertEquals(['bar'], $message2->getHeader('foo'));
        $this->assertEquals(['bar'], $message2->getHeader('Foo'));
        $this->assertEquals('bar', $message2->getHeaderLine('foo'));
        
        $message3 = $message2->withAddedHeader('FOO', 'baz');
        $this->assertTrue($message3->hasHeader('Foo'));
        $this->assertEquals(['bar', 'baz'], $message3->getHeader('Foo'));
        $this->assertEquals('bar, baz', $message3->getHeaderLine('foo'));
        
        $message4 = $message3->withoutHeader('foo');
        
        $this->assertFalse($message4->hasHeader('foo'));
        $this->assertEquals([], $message4->getHeader('Foo'));
        $this->assertEquals('', $message4->getHeaderLine('FOO'));
    }
    
    public function testProtocolVersion()
    {
        $message1 = $this->getInstance()->withProtocolVersion('1.0');
        
        $this->assertEquals('1.0', $message1->getProtocolVersion());
    }
    
    public function testBody()
    {
        $message1 = $this->getInstance()->withBody(new StringStream('foo'));
        
        $this->assertEquals('foo', $message1->getBody());
    }
}
