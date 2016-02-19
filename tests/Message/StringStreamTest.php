<?php
namespace Jivoo\Http\Message;

class StringStreamTest extends \Jivoo\TestCase
{
    
    public function testReading()
    {
        $str = new StringStream('foobar');
        $this->assertTrue($str->isReadable());
        $this->assertTrue($str->isSeekable());
        $this->assertEquals('foobar', $str);
        $str->rewind();
        $this->assertEquals('foobar', $str->getContents());
        $str->rewind();
        $this->assertEquals(6, $str->getSize());
        $this->assertEquals('foo', $str->read(3));
        $this->assertEquals('bar', $str->read(5));
        $this->assertTrue($str->eof());
        $str->rewind();
        $this->assertEquals('foo', $str->read(3));
        $this->assertEquals('3', $str->tell());
        $str->seek(2);
        $this->assertEquals('oba', $str->read(3));
        $str->seek(-2, SEEK_END);
        $this->assertEquals('ar', $str->read(3));
        $str->seek(2);
        $str->seek(2, SEEK_CUR);
        $this->assertEquals('ar', $str->read(3));
        $str->close();
        $this->assertFalse($str->isReadable());
    }
    
    public function testWriting()
    {
        $str = new StringStream('foobar');
        $this->assertTrue($str->isWritable());
        $str->seek(3);
        $this->assertEquals(4, $str->write('bazz'));
        $this->assertEquals('', $str->getContents());
        $this->assertEquals('foobazz', $str);
        $this->assertEquals(7, $str->getSize());
    }
    
    public function testMetadata()
    {
        $str = new StringStream('foobar');
        $this->assertEmpty($str->getMetadata());
        $this->assertNull($str->getMetadata('foo'));
    }
}
