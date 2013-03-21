<?php
namespace Arsenal\TestFramework\Assertions;

use Arsenal\TestFramework\TestException;

class Assertion
{
    private $val;
    private $message;
    private $not = false;
    
    public function __construct($val, $message = null)
    {
        $this->val = $val;
        $this->message = $message;
    }
    
    public function __call($name, $args)
    {
        $method = '_'.$name;
        if( ! method_exists($this, $method))
            throw new \BadMethodCallException("Invalid assertion method: $name");
        
        array_unshift($args, $this->val);
        $result = call_user_func_array(array($this, $method), $args);
        if(( ! $this->not and $result === false) or ($this->not and $result === true))
        {
            $assertion = basename(get_class($this)).'::'.$name;
            if($this->not)
                $assertion .= ' (not)';
            throw new TestException('fail', $assertion, $this->message);
        }
        
        $this->not = false;
        return $this;
    }
    
    public function not()
    {
        $this->not = true;
        return $this;
    }
    
    public function _is($val, $val2)
    {
        return $val === $val2;
    }
    
    public function _isEqual($val, $val2)
    {
        return $this->_is($val, $val2);
    }
}