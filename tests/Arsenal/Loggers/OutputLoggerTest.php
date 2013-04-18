<?php
namespace Arsenal\Loggers;

abstract class OutputLoggerTest extends LoggerTest
{
    public function setup()
    {
        ob_start();
    }
    
    public function teardown()
    {
        ob_end_clean();
    }
    
    public function testOutput()
    {
        $log = $this->getLogger();
        
        $log->critical('Something just happened.');
        $output = ob_get_contents();
        
        $this->assertTrue(strpos($output, 'Something just happened.') !== false, 'Output does not contain logged message.');
    }
    
    public function testOutputPresence()
    {
        $log = $this->getLogger();
        
        $log->debug('Some message.');
        $output = ob_get_contents();
        
        $this->assertNotEmpty($output, 'Should produce output.');
    }
    
    public function testInnactiveLevel()
    {
        $log = $this->getLogger();
        $log->setLevel('info');
        
        $log->debug('This message should not be logged.');
        $output = ob_get_contents();
        
        $this->assertEmpty($output, 'Innactive levels should not be logged.');
    }
}