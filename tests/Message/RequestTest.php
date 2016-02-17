<?php
namespace Jivoo\Http\Message;

class RequestTest extends MessageTest
{
    protected function getInstance()
    {
        return new Request(new Uri('/'));
    }
    
    public function testConstructors()
    {
        $request1 = Request::create('http://example.com', 'POST', ['foo' => 'bar']);
        
        $this->assertEquals('example.com', $request1->getHeaderLine('Host'));
        $this->assertEquals(['foo' => 'bar'], $request1->getParsedBody());
        
        $request2 = Request::create('http://example.com', 'GET', ['foo' => 'bar']);
        
        $this->assertEquals(['foo' => 'bar'], $request2->getQueryParams());
        
        $_SERVER = ['REQUEST_URI' => 'http://example.com', 'REQUEST_METHOD' => 'POST'];
        $_POST = ['foo' => 'bar'];
        $_GET = ['baz' => 'foobar'];
        $_COOKIE = ['bar' => 'bazbar'];
        $_FILES = [
          'form' => [
              'files' => [
                  'tmp_name' => ['foo', 'bar'],
                  'name' => ['foo', 'bar'],
                  'size' => [52, 16],
                  'type' => ['baz', 'foobar'],
                  'error' => [0, 0]
              ]
          ]
        ];
        
        $request3 = Request::createGlobal();
        $this->assertEquals('example.com', $request3->getHeaderLine('Host'));
        $this->assertEquals('POST', $request3->getMethod());
        $this->assertEquals(['foo' => 'bar'], $request3->getParsedBody());
        $this->assertEquals(['baz' => 'foobar'], $request3->getQueryParams());
        $this->assertEquals(['bar' => 'bazbar'], $request3->getCookieParams());
        $this->assertEquals($_SERVER, $request3->getServerParams());
        $this->assertArrayHasKey('form', $request3->getUploadedFiles());
        $this->assertCount(2, $request3->getUploadedFiles()['form']['files']);
        $this->assertInstanceOf('Jivoo\Http\Message\UploadedFile', $request3->getUploadedFiles()['form']['files'][0]);
        $this->assertInstanceOf('Jivoo\Http\Message\UploadedFile', $request3->getUploadedFiles()['form']['files'][1]);
    }
}
