<?php
namespace Arsenal\Http;

class PathPatternTest extends \PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $p = new PathPattern('/foo/bar');
        $this->assertTrue($p->match('/foo/bar'));
        $this->assertTrue($p->match('/foo/bar/'));
        $this->assertTrue($p->match('foo/bar'));
        $this->assertFalse($p->match('/bar/biz'));
        
        $p = new PathPattern('/');
        $this->assertTrue($p->match('/'));
        $this->assertTrue($p->match(''));
        $this->assertTrue($p->match('//'));
        $this->assertFalse($p->match('/foo/bar'));
    }
    
    public function testPlaceholders()
    {
        $p = new PathPattern('/foo/bar/{biz}');
        $this->assertTrue($p->match('/foo/bar/4'));
        $this->assertTrue($p->match('/foo/bar/triz'));
        $this->assertTrue($p->match('foo/bar/54-triz'));
        $this->assertFalse($p->match('/foo/bar'));
        $this->assertFalse($p->match('/foo/bar/'));
        $this->assertFalse($p->match('/foo/bar/5/triz'));
        
        $p = new PathPattern('{foo}');
        $this->assertTrue($p->match('bar'));
        $this->assertTrue($p->match('/bar'));
        $this->assertTrue($p->match('/3f42a35'));
        $this->assertFalse($p->match('/foo/bar'));
        $this->assertFalse($p->match('/'));
    }
    
    public function testOptionalPlaceholders()
    {
        $p = new PathPattern('/foo/bar/{biz?}');
        $this->assertTrue($p->match('/foo/bar'));
        $this->assertTrue($p->match('/foo/bar/'));
        $this->assertTrue($p->match('/foo/bar/3'));
        $this->assertTrue($p->match('/foo/bar/biz'));
        $this->assertFalse($p->match('/foo'));
        $this->assertFalse($p->match('/foo/bar/triz/kaz'));
        $this->assertFalse($p->match('/foo/bar/3/4'));
        
        $p = new PathPattern('/foo/bar/{biz?}/kaz');
        $this->assertTrue($p->match('/foo/bar/biz/kaz'));
        $this->assertFalse($p->match('/foo/bar'));
        
        $p = new PathPattern('/foo/bar/{biz?}/{kaz}/{triz}');
        $this->assertTrue($p->match('/foo/bar/biz/kaz/triz'));
        $this->assertTrue($p->match('/foo/bar/kaz/triz'));
        $this->assertFalse($p->match('/foo/bar/kaz'));
        // absent optional behaves like it doesn't exist, pulling the rest back
    }
    
    public function testAsterisk()
    {
        $p = new PathPattern('/foo/*');
        $this->assertTrue($p->match('/foo'));
        $this->assertTrue($p->match('/foo/bar'));
        $this->assertTrue($p->match('/foo/bar/biz'));
        $this->assertFalse($p->match('/fo'));
        $this->assertFalse($p->match('/bar'));
        $this->assertFalse($p->match('/'));
        
        $p = new PathPattern('*.png');
        $this->assertTrue($p->match('image.png'));
        $this->assertTrue($p->match('/foo/bar/image.png'));
        $this->assertFalse($p->match('/foo/bar/image.jpg'));
        $this->assertFalse($p->match('image.gif'));
        $this->assertFalse($p->match('/'));
    }
    
    public function testRegex()
    {
        $p = new PathPattern('~.*\/?(.+\.jpg)');
        $this->assertTrue($p->match('/foo/bar/image.jpg'));
        $this->assertTrue($p->match('image.jpg'));
        $this->assertFalse($p->match('image.gif'));
        $this->assertFalse($p->match('image.jpg/foo'));
    }
    
    public function testRegexIsAgainstNormalizedPath()
    {
        $p = new PathPattern('~.*\/?(.+\.jpg)');
        $this->assertTrue($p->match('/foo/bar/image.jpg/'));
    }
    
    public function testSimpleMatches()
    {
        $p = new PathPattern('foo/bar/{biz}/kaz/{tar}/{mil?}');
        $m = array();
        $p->match('/foo/bar/lor/kaz/mit', $m);
        
        $this->assertTrue(count($m) === 2);
        $this->assertTrue($m['biz'] === 'lor');
        $this->assertTrue($m['tar'] === 'mit');
        // absent optional doesn't get a match
    }
    
    public function testRegexMatches()
    {
        $p = new PathPattern('~.*\/(.+\.jpg)');
        $m = array();
        $p->match('/foo/bar/image.jpg', $m);
        
        $this->assertTrue(count($m) === 1);
        $this->assertTrue($m[0] === 'image.jpg');
        
        
        $p = new PathPattern('~/foo/bar/(.+)/biz/(.+)');
        $m = array();
        $p->match('/foo/bar/triz/biz/kar', $m);
        
        $this->assertTrue(count($m) === 2);
        $this->assertTrue($m[0] === 'triz');
        $this->assertTrue($m[1] === 'kar');
    }
}