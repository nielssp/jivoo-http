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
        
        $cookie = new MutableCookie('foo', 'bar');
        
        $pool->add($cookie);
        
        $this->assertSame($cookie, $pool['foo']);
    }
}
