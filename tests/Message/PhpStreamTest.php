<?php
namespace Jivoo\Http\Message;

class PhpStreamTest extends \Jivoo\TestCase
{
    
    /**
     * @var PhpStream
     */
    protected $stream;
    
    protected function setUp()
    {
        $this->stream = new PhpStream(fopen('tests/data/test', 'w+'));
    }
    
    protected function tearDown()
    {
        $this->stream->close();
    }
    
    public function testReading()
    {
        $this->stream->write('foobar');
        $this->stream->rewind();
        
        $this->assertTrue($this->stream->isReadable());
        $this->assertTrue($this->stream->isSeekable());
        $this->assertEquals('foobar', $this->stream);
        $this->stream->rewind();
        $this->assertEquals('foobar', $this->stream->getContents());
        $this->stream->rewind();
        $this->assertEquals(6, $this->stream->getSize());
        $this->assertEquals('foo', $this->stream->read(3));
        $this->assertEquals('bar', $this->stream->read(5));
        $this->assertTrue($this->stream->eof());
        $this->stream->rewind();
        $this->assertEquals('foo', $this->stream->read(3));
        $this->assertEquals('3', $this->stream->tell());
        $this->stream->seek(2);
        $this->assertEquals('oba', $this->stream->read(3));
        $this->stream->seek(-2, SEEK_END);
        $this->assertEquals('ar', $this->stream->read(3));
        $this->stream->seek(2);
        $this->stream->seek(2, SEEK_CUR);
        $this->assertEquals('ar', $this->stream->read(3));
        $this->assertTrue(is_array($this->stream->getMetadata()));
    }
    
    public function testWriting()
    {
        $this->assertTrue($this->stream->isWritable());
        $this->stream->write('foobar');
        $this->stream->rewind();
        
        $this->stream->seek(3);
        $this->assertEquals(4, $this->stream->write('bazz'));
        $this->assertEquals('', $this->stream->getContents());
        $this->assertEquals('foobazz', $this->stream);
        $this->assertEquals(7, $this->stream->getSize());
    }
}
