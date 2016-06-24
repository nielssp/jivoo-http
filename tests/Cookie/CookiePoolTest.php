<?php
namespace Jivoo\Http\Cookie;

use Jivoo\TestCase;

class CookiePoolTest extends TestCase
{
    
    public function testArrayAccess()
    {
        $pool = new CookiePool();
        
        $this->assertInstanceOf('Jivoo\Http\Cookie\MutableCookie', $pool['foo']);
        $this->assertEquals('', $pool['foo']->get());
        $this->assertFalse(isset($pool['foo']));
        
        $pool['bar'] = 'baz';
        
        $this->assertEquals('baz', $pool['bar']->get());
        $this->assertTrue(isset($pool['bar']));
        $pool['bar']->setPath('/foo');
        $this->assertEquals('/foo', $pool['bar']->getPath());
        
        unset($pool['bar']);
        $this->assertEquals('', $pool['bar']->get());
        $this->assertFalse(isset($pool['bar']));
    }
    
    public function testAdd()
    {
        $pool = new CookiePool();
        
        $cookie1 = new MutableCookie('foo', 'bar');
        
        $pool->add($cookie1);
        $this->assertSame($cookie1, $pool['foo']);
        
        $cookie2 = $this->getMockBuilder('Jivoo\Http\Cookie\ResponseCookie')->getMock();
        
        $cookie2->method('getName')->willReturn('baz');
        $cookie2->method('get')->willReturn('foobar');
        $cookie2->method('getPath')->willReturn('/baz/bar');
        
        $pool->add($cookie2);
        $this->assertTrue(isset($pool['baz']));
        $this->assertEquals('foobar', $pool['baz']->get());
        $this->assertEquals('/baz/bar', $pool['baz']->getPath());
        
        $cookie3 = $this->getMockBuilder('Jivoo\Http\Cookie\Cookie')->getMock();
        
        $cookie3->method('getName')->willReturn('bar');
        $cookie3->method('get')->willReturn('baz');
        
        $pool->add($cookie3);
        $this->assertTrue(isset($pool['bar']));
        $this->assertEquals('baz', $pool['bar']->get());
        
        $array = iterator_to_array($pool);
        $this->assertCount(3, $array);
    }
}
