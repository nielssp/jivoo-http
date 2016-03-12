<?php
namespace Jivoo\Http\Cookie;

use Jivoo\TestCase;

class MutableCookieTest extends TestCase
{
    
    public function testConstructor()
    {
        $cookie = new MutableCookie('foo');
        
        $this->assertEquals('foo', $cookie->getName());
        $this->assertEquals('', $cookie->get());
        $this->assertEquals('', $cookie);
        $this->assertFalse($cookie->hasChanged());
        
        $cookie = new MutableCookie('foo', 'bar');
        
        $this->assertEquals('foo', $cookie->getName());
        $this->assertEquals('bar', $cookie->get());
        $this->assertEquals('bar', $cookie);
        $this->assertFalse($cookie->hasChanged());
    }
    
    /**
     * @dataProvider getterSetterProvider
     */
    public function testGettersAndSetters($getter, $setter, $value)
    {
        $cookie = new MutableCookie('foo', 'bar');
        
        $cookie->$setter($value);
        $this->assertEquals($value, $cookie->$getter());
        $this->assertTrue($cookie->hasChanged());
    }
    
    public function getterSetterProvider()
    {
        return [
            ['get', 'set', 'baz'],
            ['getPath', 'setPath', '/foo'],
            ['getDomain', 'setDomain', 'example.com'],
            ['isSecure', 'setSecure', true],
            ['isHttpOnly', 'setHttpOnly', true]
        ];
    }
    
    public function testExpiration()
    {
    
        $cookie = new MutableCookie('foo', 'bar');
        $cookie->expiresAt(\DateTime::createFromFormat('U', 42));
        $this->assertEquals(42, $cookie->getExpiration()->getTimestamp());
        $this->assertTrue($cookie->hasChanged());

        $cookie = new MutableCookie('foo', 'bar');
        
        $start = time();
        $cookie->expiresAfter(100);
        $end = time();
        $this->assertGreaterThanOrEqual($start + 100, $cookie->getExpiration()->getTimestamp());
        $this->assertLessThanOrEqual($end + 100, $cookie->getExpiration()->getTimestamp());
        $this->assertTrue($cookie->hasChanged());
        
        $start = time();
        $cookie->expiresAfter(-100);
        $end = time();
        $this->assertGreaterThanOrEqual($start - 100, $cookie->getExpiration()->getTimestamp());
        $this->assertLessThanOrEqual($end - 100, $cookie->getExpiration()->getTimestamp());
        $this->assertTrue($cookie->hasChanged());
    }
}
