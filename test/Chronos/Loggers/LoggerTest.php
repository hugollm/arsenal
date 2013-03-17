<?php
namespace Chronos\Loggers;

use Chronos\TestFramework\Assert;
use InvalidArgumentException;

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
        catch(InvalidArgumentException $e)
        {
            Assert::pass();
        }
    }
    
    public function getSupportedLevels()
    {
        $count = count($this->logger->getSupportedLevels());
        Assert::true($count === 6);
    }
}