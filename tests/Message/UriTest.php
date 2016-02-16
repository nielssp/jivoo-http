<?php
namespace Jivoo\Http\Message;

class UriTest extends \Jivoo\TestCase
{
   
    public function testConstructor()
    {
        $uri = new Uri('http://user:password@example.com:81/foo/bar?a=2&b=3#baz');
        
        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('/foo/bar', $uri->getPath());
        $this->assertEquals(81, $uri->getPort());
        $this->assertEquals('user:password', $uri->getUserInfo());
        $this->assertEquals('a=2&b=3', $uri->getQuery());
        $this->assertEquals('baz', $uri->getFragment());
    }
    
    public function testToString()
    {
        $uri1 = new Uri('http://user:password@example.com:81/foo/bar?a=2&b=3#baz');
        
        $this->assertEquals('http://user:password@example.com:81/foo/bar?a=2&b=3#baz', $uri1);
        
        $uri2 = $uri1->withPath('foo/bar');
        
        $this->assertEquals('http://user:password@example.com:81/foo/bar?a=2&b=3#baz', $uri2);
        
        $this->assertEquals('foo/bar', new Uri('foo/bar'));
    }
    
    public function testAuthority()
    {
        $uri1 = new Uri('http://user:password@example.com:81');
        
        $this->assertEquals('user:password@example.com:81', $uri1->getAuthority());
    }
    
    public function testPort()
    {
        $uri1 = new Uri('http://example.com:80');
        $uri2 = new Uri('http://example.com:443');
        $uri3 = new Uri('https://example.com:80');
        $uri4 = new Uri('https://example.com:443');
        
        $this->assertEquals('', $uri1->getPort());
        $this->assertEquals(443, $uri2->getPort());
        $this->assertEquals(80, $uri3->getPort());
        $this->assertEquals('', $uri4->getPort());
    }
    
    public function testGettersAndSetters()
    {
        $uri1 = new Uri('');
        $uri2 = $uri1->withScheme('HTTP')
            ->withUserInfo('foo', 'bar')
            ->withHost('EXAMPLE.com')
            ->withPort(81)
            ->withPath('/FOO/bar')
            ->withQuery('a=2&b=3')
            ->withFragment('foobar');
        
        $this->assertEquals('http', $uri2->getScheme());
        $this->assertEquals('foo:bar', $uri2->getUserInfo());
        $this->assertEquals('example.com', $uri2->getHost());
        $this->assertEquals(81, $uri2->getPort());
        $this->assertEquals('/foo/bar', $uri2->getPath());
        $this->assertEquals('a=2&b=3', $uri2->getQuery());
        $this->assertEquals('foobar', $uri2->getFragment());
    }
}
