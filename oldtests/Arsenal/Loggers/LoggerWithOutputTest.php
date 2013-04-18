<?php
namespace Arsenal\Loggers;

abstract class LoggerWithOutputTest extends LoggerTest
{
    public function setUp()
    {
        $this->logger = $this->getLogger();
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
        $this->assertString(ob_get_contents())->not()->isEmpty();
        ob_end_clean();
    }
    
    public function maxLevelWorks()
    {
        ob_start();
        
        $this->logger->setMaxLevel('info');
        $this->logger->debug('just a test');
        $this->assertString(ob_get_contents())->isEmpty();
        
        $this->logger->info('just a test');
        $this->assertString(ob_get_contents())->not()->isEmpty();
        
        ob_end_clean();
    }
}