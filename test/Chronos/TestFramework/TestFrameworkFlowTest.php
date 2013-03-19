<?php
namespace Chronos\TestFramework;

class TestFrameworkFlowTest
{
    private $setUp = 0;
    private $tearDown = 0;
    
    public function __construct()
    {
        static $construct = 0;
        $construct++;
        if($construct !== 1)
            throw new RunTimeException('TestFramework not running properly. Instantiating test two times perhaps?');
    }
    
    public function __destruct()
    {
        static $destruct = 0;
        $destruct++;
        if($destruct !== 1)
            throw new RunTimeException('TestFramework not running properly. Instantiating test two times perhaps?');
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
        Assert::isTrue($this->setUp === 1);
        Assert::isTrue($this->tearDown === 0);
    }
    
    public function _withUnderline()
    {
        Assert::isTrue($this->setUp === 2);
        Assert::isTrue($this->tearDown === 1);
    }
    
    public function anotherTest()
    {
        Assert::isTrue($this->setUp === 3);
        Assert::isTrue($this->tearDown === 2);
    }
    
    private function privateMethod()
    {
        Assert::fail('private methods should be treated as tests');
    }
    
    private function protectedMethod()
    {
        Assert::fail('protected methods should be treated as tests');
    }
}