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
    
    public function method1()
    {
        Assert::true($this->setUp === 1);
        Assert::true($this->tearDown === 0);
    }
    
    public function method2()
    {
        Assert::true($this->setUp === 2);
        Assert::true($this->tearDown === 1);
    }
    
    public function method3()
    {
        Assert::true($this->setUp === 3);
        Assert::true($this->tearDown === 2);
    }
}