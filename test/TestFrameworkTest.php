<?php

use Chronos\TestFramework\Assert;

class TestFrameworkTest
{
    private $construct = 0;
    private $setup = 0;
    private $teardown = 0;
    private $destruct = 0;
    
    public function __construct()
    {
        $this->construct++;
    }
    
    public function setUp()
    {
        $this->setup++;
    }
    
    function setUpAndTestMethods()
    {
        Assert::true($this->construct === 1);
        Assert::true($this->setup === 1);
    }
}