<?php
namespace Jivoo\Http\Route;

use Jivoo\Http\Message\Response;
use Jivoo\Http\Message\Status;
use Jivoo\TestCase;

class RouteBaseTest extends TestCase
{
    
    public function testStripAttributes()
    {
        
        $this->assertEquals('foo', RouteBase::stripAttributes('foo?bar#baz', $route));
        $this->assertEquals(['bar' => ''], $route['query']);
        $this->assertEquals('baz', $route['fragment']);
    }
}
