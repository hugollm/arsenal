<?php
namespace Arsenal\TestFramework\Assertions;

class ArrayAssertion extends Assertion
{
    public function _hasCount($val, $count)
    {
        return count($val) === $count;
    }
    
    public function _isEqual($val, $val2)
    {
        return $val === $val2;
    }
    
    public function _hasKey($val, $key)
    {
        return isset($val[$key]);
    }
}