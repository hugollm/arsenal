<?php
namespace Arsenal\TestFramework;

class TestResult
{
    private $class;
    private $method;
    private $status;
    private $assertion;
    private $message;
    private $file;
    private $line;
    
    public function __construct($class, $method, $status, $assertion, $message, $file, $line)
    {
        $this->class = $class;
        $this->method = $method;
        $this->status = $status;
        $this->assertion = $assertion;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
    }
    
    public function getClass()
    {
        return $this->class;
    }
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function getAssertion()
    {
        return $this->assertion;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function getFile()
    {
        return $this->file;
    }
    
    public function getLine()
    {
        return $this->line;
    }
}