<?php
namespace Arsenal\TestFramework;

class SessionResults
{
    public $pass = array();
    public $fail = array();
    public $skip = array();
    
    public function addResult(TestResult $result)
    {
        $status = $result->getStatus();
        $this->{$status}[] = $result;
    }
    
    public function getPass()
    {
        return $this->pass;
    }
    
    public function getFail()
    {
        return $this->fail;
    }
    
    public function getSkip()
    {
        return $this->skip;
    }
    
    public function getTotalCount()
    {
        return $this->getPassCount() + $this->getFailCount() + $this->getSkipCount();
    }
    
    public function getPassCount()
    {
        return count($this->pass);
    }
    
    public function getFailCount()
    {
        return count($this->fail);
    }
    
    public function getSkipCount()
    {
        return count($this->skip);
    }
    
    public function dump()
    {
        
        $d = new HtmlDumper;
        
        $d->setClass('test-results', 'font-family:monospace;');
        $d->setClass('header', 'padding:5px;');
        $d->setClass('pass-header', 'background-color:#ada;');
        $d->setClass('skip-header', 'background-color:#bbe;');
        $d->setClass('fail-header', 'background-color:#eaa;');
        $d->setClass('detail', 'padding:5px;background-color:#f4f4f4;');
        $d->setClass('test-results td', 'padding:5px;padding-right:20px;');
        
        $d->open('div', 'test-results');
            
            $results = array_merge($this->fail, $this->skip, $this->pass);
            $status = null;
            
            $d->open('table');
            foreach($results as $i=>$result)
            {
                $newStatus = $result->getStatus();
                if($status !== $newStatus)
                    $d->open('tr', $newStatus.'-header')
                        ->node('td', '', ucfirst($newStatus).': '.count($this->{$newStatus}))
                        ->node('td')
                        ->node('td')
                        ->node('td')
                        ->node('td')
                    ->close('tr');
                $status = $newStatus;
                
                if($status === 'pass')
                    continue;
                
                $d->open('tr');
                    $d->node('td', 'detail', basename($result->getClass()));
                    $d->node('td', 'detail', $result->getMethod());
                    $d->node('td', 'detail', $result->getAssertion());
                    if($result->getFile())
                        $d->node('td', 'detail', $result->getFile().'('.$result->getLine().')');
                    else
                        $d->node('td', 'detail');
                    $d->node('td', 'detail', $result->getMessage());
                $d->close('tr');
            }
            $d->close('table');
            
        $d->close('div');
        
        $d->dump();
    }
}