<?php
namespace Jivoo\Http\Route;

use Jivoo\Http\Message\Response;
use Jivoo\Http\Message\Status;
use Jivoo\TestCase;

class RouteBaseTest extends TestCase
{
    public function testAttributes()
    {
        $route1 = $this->getMockForAbstractClass('Jivoo\Http\Route\RouteBase');
        $route2 = $route1->withQuery(['foo' => 'bar'])
            ->withParameters(['baz'])
            ->withFragment('foobar');
        $route3 = $route2->withoutAttributes();
        
        $this->assertEquals([], $route1->getQuery());
        $this->assertEquals([], $route1->getParameters());
        $this->assertEquals('', $route1->getFragment());
        
        $this->assertEquals(['foo' => 'bar'], $route2->getQuery());
        $this->assertEquals(['baz'], $route2->getParameters());
        $this->assertEquals('foobar', $route2->getFragment());
        
        $this->assertEquals([], $route3->getQuery());
        $this->assertEquals([], $route3->getParameters());
        $this->assertEquals('', $route3->getFragment());
    }
    
    public function testStripAttributes()
    {
        
        $this->assertEquals('foo', RouteBase::stripAttributes('foo?bar#baz', $route));
        $this->assertEquals(['bar' => ''], $route['query']);
        $this->assertEquals('baz', $route['fragment']);
    }
    
    public function testInsertParameters()
    {
        $this->assertEquals([], RouteBase::insertParameters([], []));
        $this->assertEquals(['foo', 'bar'], RouteBase::insertParameters(['foo', 'bar'], []));
        $this->assertEquals(['foo', 'bar'], RouteBase::insertParameters(['foo', '*'], ['bar', 'baz']));
        $this->assertEquals(['foo', 'bar'], RouteBase::insertParameters(['foo', ':0'], ['bar', 'baz']));
        $this->assertEquals(['foo', 'baz'], RouteBase::insertParameters(['foo', ':1'], ['bar', 'baz']));
        $this->assertEquals(['foo', 'bar', 'baz'], RouteBase::insertParameters(['foo', '**'], ['bar', 'baz']));
        $this->assertEquals(['baz', 'foobar', 'bazbar'], RouteBase::insertParameters(
            [':foo', ':bar', 'bazbar'],
            ['bar' => 'foobar', 'foo' => 'baz']
        ));
        $this->assertEquals(['baz', 'foo', 'foobar', 'bazbar'], RouteBase::insertParameters(
            [':foo', ':*'],
            ['foo', 'bar' => 'foobar', 'foo' => 'baz', 'bazbar']
        ));
    }
}
