<?php
namespace Arsenal\TestFramework;

class FlowTest extends TestFixture
{
    private $setUp = 0;
    private $tearDown = 0;
    
    public function __construct()
    {
        static $construct = 0;
        $construct++;
        if($construct !== 1)
            throw new \RunTimeException('TestFramework not running properly. Instantiating test two times perhaps?');
    }
    
    public function __destruct()
    {
        static $destruct = 0;
        $destruct++;
        if($destruct !== 1)
            throw new \RunTimeException('TestFramework not running properly. Instantiating test two times perhaps?');
    }
    
    public function setUp()
    {
        $this->setUp++;
    }
    
    public function tearDown()
    {
        $this->tearDown++;
    }
    
    public function testMethod()
    {
        $this->assertTrue($this->setUp === 1);
        $this->assertTrue($this->tearDown === 0);
    }
    
    public function anotherTest()
    {
        $this->assertTrue($this->setUp === 2);
        $this->assertTrue($this->tearDown === 1);
    }
    
    private function privateMethod()
    {
        $this->fail('private methods should be treated as tests');
    }
    
    protected function protectedMethod()
    {
        $this->fail('protected methods should be treated as tests');
    }
}