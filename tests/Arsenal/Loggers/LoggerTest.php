<?php
namespace Arsenal\Loggers;

abstract class LoggerTest extends \PHPUnit_Framework_TestCase
{
    abstract protected function getLogger();
    
    public function testAllLevels()
    {
        $log = $this->getLogger();
        $log->debug('Lorem ipsum dolor sit amet.');
        $log->info('Lorem ipsum dolor sit amet.');
        $log->notice('Lorem ipsum dolor sit amet.');
        $log->warning('Lorem ipsum dolor sit amet.');
        $log->error('Lorem ipsum dolor sit amet.');
        $log->critical('Lorem ipsum dolor sit amet.');
    }
}