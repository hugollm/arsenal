<?php
namespace Chronos\Loggers;

use Chronos\TestFramework\Assert;

abstract class LoggerTest
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
            Assert::pass();
        }
    }
    
    public function setMaxLevel()
    {
        $this->logger->setMaxLevel('warning');
        $maxLevel = $this->logger->getMaxLevel();
        Assert::isTrue($maxLevel === 'warning');
    }
    
    public function getSupportedLevels()
    {
        $levels = $this->logger->getSupportedLevels();
        Assert::isArray($levels)->hasCount(6);
    }
}