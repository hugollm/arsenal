<?php
namespace Chronos\TestFramework;

class TestException extends \Exception
{
    protected $message = '';
    private $status = null;
    private $testFile = null;
    private $testLine = null;
    
    public function __construct($status, $assertion, $message = null)
    {
        if( ! in_array($status, array('pass', 'fail', 'skip')))
            throw new \InvalidArgumentException('TestResult status should be one of: pass|fail|skip');
        
        $this->status = $status;
        $this->assertion = $assertion;
        $this->message = (string)$message;
        $this->parseTrace();
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function getAssertion()
    {
        return $this->assertion;
    }
    
    public function getTestFile()
    {
        return $this->testFile;
    }
    
    public function getTestLine()
    {
        return $this->testLine;
    }
    
    private function parseTrace()
    {
        $trace = $this->getTrace();
        
        foreach($trace as $step)
            if( ! empty($step['class']) and ($step['class'] == 'Chronos\\TestFramework\\Assert' or $step['class'] == 'Chronos\\TestFramework\\Assertions\\Assertion'))
            {
                $this->class = $step['class'];
                $this->method = $step['function'];
                $this->testFile = ! empty($step['file']) ? $step['file'] : null;
                $this->testLine = ! empty($step['line']) ? $step['line'] : null;
            }
    }
}