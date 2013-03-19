<?php
namespace Chronos\Loggers;

use Chronos\TestFramework\Assert;

class HtmlLoggerTest extends LoggerTest
{
    public function setUp()
    {
        $this->logger = new HtmlLogger;
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
        Assert::isTrue(ob_get_contents());
        ob_end_clean();
    }
    
    public function maxLevelWorks()
    {
        ob_start();
        
        $this->logger->setMaxLevel('info');
        $this->logger->debug('just a test');
        Assert::isFalse(ob_get_contents());
        
        $this->logger->info('just a test');
        Assert::isTrue(ob_get_contents());
        
        ob_end_clean();
    }
}