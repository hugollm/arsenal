<?php
namespace Arsenal\TestFramework\Assertions;

class ObjectAssertion extends Assertion
{
    public function _isClass($obj, $class)
    {
        $getClass = get_class($obj);
        $getClass = strtolower($getClass);
        $class = strtolower($class);
        
        return $getClass === $class;
    }
    
    public function _isSubClass($obj, $class)
    {
        return is_subclass_of($obj, $class);
    }
    
    public function _hasProperty($obj, $key)
    {
        return isset($obj->$key);
    }
}