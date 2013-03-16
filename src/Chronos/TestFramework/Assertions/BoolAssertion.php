<?php
namespace Chronos\TestFramework\Assertions;

class BoolAssertion extends Assertion
{
    public function _isTrue($val)
    {
        return (bool)$val;
    }
    
    public function _isFalse($val)
    {
        return ! $val;
    }
}