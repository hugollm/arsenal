<?php
namespace Arsenal\Loggers;

use Arsenal\TestFramework\TestFixture;

abstract class LoggerTest extends TestFixture
{
    protected $logger;
    
    public function usage()
    {
        $this->logger->debug('test message');
        $this->logger->info('test message');
        $this->logger->notice('test message');
        $this->logger->warning('test message');
        $this->logger->error('test message');
        $this->logger->critical('test message');
    }
    
    public function invalidLevelException()
    {
        try
        {
            $this->logger->log(0, 'test message');
        }
        catch(\InvalidArgumentException $e)
        {
            $this->pass();
        }
    }
    
    public function setMaxLevel()
    {
        $this->logger->setMaxLevel('warning');
        $maxLevel = $this->logger->getMaxLevel();
        $this->assertTrue($maxLevel === 'warning');
    }
    
    public function getSupportedLevels()
    {
        $levels = $this->logger->getSupportedLevels();
        $this->assertArray($levels)->hasCount(6);
    }
    
    abstract protected function getLogger();
}