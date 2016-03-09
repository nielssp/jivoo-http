<?php
namespace Jivoo\Http\Route;

use Jivoo\Http\Message\Response;
use Jivoo\Http\Message\Status;
use Jivoo\TestCase;

class AssetSchemeTest extends TestCase
{
    
    public function testFind()
    {
        $assets = new AssetScheme('tests/data/assets');
        
        $this->assertEquals('tests/data/assets/css/foo.css', $assets->find('css/foo.css'));
        $this->assertNull($assets->find('foo/css/bar.css'));
        
        $assets->addPath('foo', 'tests/data/foo');
        $this->assertEquals('tests/data/assets/css/foo.css', $assets->find('css/foo.css'));
        $this->assertEquals('tests/data/foo/css/bar.css', $assets->find('foo/css/bar.css'));
        
        $assets->addPath('', 'tests/data/foo');
        $this->assertEquals('tests/data/foo/css/bar.css', $assets->find('css/bar.css'));
    }
    
    public function testRoute()
    {
        $assets = new AssetScheme('tests/data/assets');
        
        $route = $assets->fromString('asset:css/foo.css');
        $this->assertEquals(['css', 'foo.css'], $route->getParameters());
        $this->assertEquals('asset:css/foo.css', $route->__toString());
        
        $this->assertEquals(['css', 'foo.css'], $route->getPath(['**']));
        
        $response = $route->dispatch(
            new \Jivoo\Http\ActionRequest(\Jivoo\Http\Message\Request::create('/assets/css/foo.css')),
            new Response(Status::OK)
        );
        
        $this->assertEquals('/* Empty test file used in AssetSchemeTest */', $response->getBody()->getContents());
    }
}
