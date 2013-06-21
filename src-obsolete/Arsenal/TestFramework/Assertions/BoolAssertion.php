<?php
namespace Arsenal\TestFramework\Assertions;

class BoolAssertion extends Assertion
{
    public function _isTrue($val, $strict = false)
    {
        if( ! $strict)
            $val = (bool)$val;
        return $val === true;
    }
    
    public function _isFalse($val, $strict = false)
    {
        if( ! $strict)
            $val = (bool)$val;
        return $val === false;
    }
    
    public function _isNull($val)
    {
        return is_null($val);
    }
}