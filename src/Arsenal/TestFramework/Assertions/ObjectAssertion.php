<?php
namespace Arsenal\TestFramework\Assertions;

class ObjectAssertion extends Assertion
{
    public function _isClass($val, $class)
    {
        $getClass = get_class($val);
        $getClass = strtolower($getClass);
        $class = strtolower($class);
        
        return $getClass === $class;
    }
    
    public function _isSubClass($val, $class)
    {
        return is_subclass_of($val, $class);
    }
}