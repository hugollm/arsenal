<?php
namespace Chronos\Loggers;

use Chronos\TestFramework\Assert;

class JSConsoleLoggerTest extends LoggerTest
{
    public function setUp()
    {
        $this->logger = new JSConsoleLogger;
        ob_start();
    }
    
    public function tearDown()
    {
        ob_end_clean();
    }
    
    public function producesOutput()
    {
        ob_start();
        $this->logger->debug('just a test');
        Assert::true(ob_get_contents());
        ob_end_clean();
    }
}