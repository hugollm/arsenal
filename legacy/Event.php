<?php

class Event
{
    private static $listeners = array();
    
    public static function listen($name, $callback)
    {
        if( ! is_callable($callback))
            throw new InvalidArgumentException('Second argument of Event::listen must be a valid callback.');
        
        if( ! isset($listeners[$name]))
            $listeners[$name] = array();
        self::$listeners[$name][] = $callback;
    }
    
    public static function fire($name)
    {
        $listeners = isset(self::$listeners[$name]) ? self::$listeners[$name] : array();
        $args = array_slice(func_get_args(), 1);
        foreach($listeners as $listener)
            call_user_func_array($listener, $args);
    }
}