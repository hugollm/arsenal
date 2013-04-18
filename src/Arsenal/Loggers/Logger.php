<?php
namespace Arsenal\Loggers;

abstract class Logger
{
    private $level = 'debug';
    
    public function getLevel()
    {
        return $this->level;
    }
    
    public function setLevel($level)
    {
        $this->level = $level;
    }
    
    public function getSupportedLevels()
    {
        return array('critical', 'error', 'warning', 'notice', 'info', 'debug');
    }
    
    public function isValidLevel($level)
    {
        if(is_string($level))
            return in_array($level, $this->getSupportedLevels());
        else
            return false;
    }
    
    public function isLevelActive($level)
    {
        return $this->levelToCode($level) <= $this->levelToCode($this->level);
    }
    
    public function critical($message)
    {
        $this->log('critical', $message);
    }
    
    public function error($message)
    {
        $this->log('error', $message);
    }
    
    public function warning($message)
    {
        $this->log('warning', $message);
    }
    
    public function notice($message)
    {
        $this->log('notice', $message);
    }
    
    public function info($message)
    {
        $this->log('info', $message);
    }
    
    public function debug($message)
    {
        $this->log('debug', $message);
    }
    
    public function log($level, $message)
    {
        if( ! $this->isValidLevel($level))
            throw new \InvalidArgumentException('Invalid log level: '.$level);
        if($this->isLevelActive($level))
            $this->commit($level, $message);
    }
    
    private function levelToCode($level)
    {
        if( ! $this->isValidLevel($level))
            throw new \InvalidArgumentException('Invalid log level: '.$level);
        
        $supportedLevels = $this->getSupportedLevels();
        return array_search($level, $supportedLevels)+1;
    }
    
    abstract protected function commit($level, $message);
}