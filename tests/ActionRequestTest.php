<?php
namespace Jivoo\Http;

use Jivoo\TestCase;

class ActionRequestTest extends TestCase
{
    
    public function testAttributes()
    {
        $request = Message\Request::create('/index.php/foo/bar')
            ->withHeader('Foo-Bar', 'foobar')
            ->withServerParams([
                'SCRIPT_NAME' => '/index.php',
                'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
            ]);
        $arequest = new ActionRequest($request);
        
        $this->assertEquals('foobar', $arequest->getHeaderLine('foo-bar'));
        $this->assertEquals('foo/bar', $arequest->getAttribute('path'));
        $this->assertTrue($arequest->accepts('text/html'));
        $this->assertTrue($arequest->accepts('image/webp'));
        $this->assertFalse($arequest->accepts('text/plain'));
        $this->assertCount(5, $arequest->accepts());
        $this->assertTrue($arequest->acceptsEncoding('gzip'));
        $this->assertFalse($arequest->acceptsEncoding('bzip2'));
        $this->assertCount(3, $arequest->acceptsEncoding());
        $this->assertTrue($arequest->isGet());
        $this->assertFalse($arequest->isPost());
        $this->assertFalse($arequest->isPut());
        $this->assertFalse($arequest->isPatch());
        $this->assertFalse($arequest->isDelete());
        $this->assertTrue($arequest->isAjax());
    }
    
    public function testFindPath()
    {
        $request = Message\Request::create('/index.php/foo/bar');
        
        $this->assertEquals('index.php/foo/bar', ActionRequest::findPath($request));
        
        $request = $request->withServerParams(['SCRIPT_NAME' => '/index.php']);
        
        $this->assertEquals('foo/bar', ActionRequest::findPath($request));
        
        $request = $request->withUri(new Message\Uri('/index.php'));
        $this->assertEquals('', ActionRequest::findPath($request));
        
        $request = $request->withUri(new Message\Uri('/index.php/'));
        $this->assertEquals('', ActionRequest::findPath($request));
    }
}
