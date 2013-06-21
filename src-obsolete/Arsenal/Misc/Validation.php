<?php
namespace Arsenal\Misc;

class Validation
{
    private static $rules = array();
    private $required = array();
    private $requiredMessage = null;
    private $binds = array();
    
    public static function register($rule, $callback)
    {
        if( ! is_callable($callback))
            throw new \InvalidArgumentException('Invalid callback for Validation::register');
        
        self::$rules[$rule] = $callback;
    }
    
    public static function test($val, $rule, array $context = array())
    {
        $callback = self::getCallback($rule);
        return call_user_func($callback, $val, $context);
    }
    
    public function required(array $keys, $message = 'required')
    {
        $this->required = array_merge($this->required, $keys);
        $this->requiredMessage = $message;
    }
    
    public function bind($key, $rule, $message = 'invalid')
    {
        if( ! is_callable($rule) and ! isset(self::$rules[$rule]))
            throw new \InvalidArgumentException('Validation rule "'.$rule.'" was not registered and is not callable');
        
        $this->binds[] = array('key'=>$key, 'rule'=>$rule, 'message'=>$message);
    }
    
    public function testArray(array $vals, array &$errors = array())
    {
        // searching for not present required keys
        foreach($this->required as $key)
            if( ! isset($vals[$key]))
                $errors[$key] = $this->requiredMessage;
        
        foreach($this->binds as $bind)
        {
            // this key already got an error
            if(isset($errors[$bind['key']]))
                continue;
            
            // running test if key is present
            if(isset($vals[$bind['key']]) and self::test($vals[$bind['key']], $bind['rule'], $vals) === false)
                $errors[$bind['key']] = $bind['message'];
        }
        
        return ! count($errors);
    }
    
    private static function getCallback($rule)
    {
        if(is_string($rule) and isset(self::$rules[$rule]))
            return self::$rules[$rule];
        if(is_callable($rule))
            return $rule;
        
        throw new \InvalidArgumentException('Validation rule "'.(string)$rule.'" was not registered and is not callable');
    }
}

