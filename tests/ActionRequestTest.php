<?php
namespace Jivoo\Http;

use Jivoo\TestCase;

class ActionRequestTest extends TestCase
{
    
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
